<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$logado = isset($_SESSION['usuario_id']);

$total_registros = 0;
$imc_atual = null;
$peso_atual = null;
$variacao_peso = null;
$ultimo_registro = null;

if ($logado) {
    require_once 'factory/conexao.php';
    $usuario_id = (int) $_SESSION['usuario_id'];

    $sql = "SELECT peso, altura, imc, classificacao, data_registro 
            FROM historico_imc 
            WHERE usuario_id = ? 
            ORDER BY data_registro DESC";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $total_registros = $resultado->num_rows;

    if ($total_registros > 0) {
        $resultado->data_seek(0);
        $primeiro = $resultado->fetch_assoc();
        $imc_atual = $primeiro['imc'];
        $peso_atual = $primeiro['peso'];
        $ultimo_registro = $primeiro;

        $resultado->data_seek($total_registros - 1);
        $ultimo = $resultado->fetch_assoc();

        if ($ultimo && isset($ultimo['peso'])) {
            $variacao_peso = (float)$peso_atual - (float)$ultimo['peso'];
        }

        $resultado->data_seek(0);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Avaliação - Monitoramento Corporal</title>
    <link rel="stylesheet" href="css/estilo.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        .resumo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .resumo-card {
            background: var(--bg-card);
            border-radius: var(--radius);
            padding: 22px;
            border: 1px solid var(--border-soft);
            box-shadow: var(--shadow-card);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }
        .resumo-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-hover);
            border-color: rgba(15, 185, 177, 0.3);
        }
        .resumo-card::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 4px; height: 100%;
            background: var(--primary);
        }
        .resumo-card h4 {
            color: var(--text-muted);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }
        .resumo-card .valor { color: #fff; font-size: 28px; font-weight: 700; }
        .resumo-card .variacao-positiva { color: #2ecc71; }
        .resumo-card .variacao-negativa { color: #e74c3c; }
        .resumo-card .variacao-neutra  { color: var(--text-muted); }

        .historico-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 15px;
        }

        .badge-total {
            background: rgba(15, 185, 177, 0.15);
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid rgba(15, 185, 177, 0.3);
        }
    </style>
</head>
<body>

    <nav class="sidebar">
        <h2>MonCorp</h2>
        <a href="index.php" class="nav-link">🧮 Calculadora</a>
        <a href="avaliacao.php" class="nav-link active">📏 Avaliação Física</a>
        <a href="treinos.php" class="nav-link">🏋️ Treinos</a>
        <a href="nutricao.php" class="nav-link">🍎 Nutrição</a>
        <a href="configuracoes.php" class="nav-link" style="margin-top: auto;">⚙️ Configurações</a>
    </nav>

    <main class="main-content">
        <header>
            <div class="header-text">
                <h1>Histórico de Avaliação</h1>
                <p>Acompanhe a sua evolução e histórico de medidas ao longo do tempo.</p>
            </div>

            <div class="profile-container">
                <?php if ($logado): ?>
                    <div class="profile-pic" onclick="toggleProfileMenu(event)">
                        <?= strtoupper(substr($_SESSION['usuario_nome'], 0, 1)) ?>
                    </div>

                    <div class="profile-dropdown" id="profileMenu">
                        <div class="dropdown-header">
                            <h4><?= htmlspecialchars($_SESSION['usuario_nome']) ?></h4>
                            <small>Configurações do Perfil</small>
                        </div>
                        <div class="config-area">
                            <a href="configuracoes.php" class="dropdown-item">⚙️ Editar Meus Dados</a>
                            <a href="index.php" class="dropdown-item">📊 Atualizar Peso/Medidas</a>
                            <a href="configuracoes.php" class="dropdown-item">🎯 Mudar Objetivo Atual</a>
                        </div>

                        <form action="factory/logout.php" method="POST" style="margin: 0;">
                            <button type="submit" class="btn-logout">Encerrar Sessão</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="model/login.php" class="btn-outline">Entrar</a>
                        <a href="model/cadastro.php" class="btn-solid">Cadastre-se</a>
                    </div>
                <?php endif; ?>
            </div>
        </header>

        <div class="area-conteudo">
            <?php if (!$logado): ?>

                <div class="msg-bloqueio">
                    <h2>⚠️ Acesso Restrito</h2>
                    <p>O histórico de evolução física é uma área exclusiva e confidencial.</p>
                    <p>Faça login ou cadastre-se para começar a registrar o seu progresso!</p>
                    <a href="model/login.php">Fazer Login</a>
                    <p style="margin-top: 15px;">Não tem uma conta?
                        <a href="model/cadastro.php" style="background: none; color: var(--primary); padding: 0;">Cadastre-se aqui</a>
                    </p>
                </div>

            <?php else: ?>

                <?php if ($total_registros > 0): ?>
                    <div class="resumo-grid">
                        <div class="resumo-card">
                            <h4>Total de Registros</h4>
                            <div class="valor"><?= $total_registros ?></div>
                        </div>

                        <div class="resumo-card">
                            <h4>Peso Atual</h4>
                            <div class="valor"><?= number_format($peso_atual, 1, ',', '.') ?> kg</div>
                        </div>

                        <div class="resumo-card">
                            <h4>IMC Atual</h4>
                            <div class="valor"><?= number_format($imc_atual, 1, ',', '.') ?></div>
                        </div>

                        <div class="resumo-card">
                            <h4>Variação de Peso</h4>
                            <?php
                                $classe_var = 'variacao-neutra';
                                $sinal = '';
                                if ($variacao_peso !== null) {
                                    if ($variacao_peso > 0.1) { $classe_var = 'variacao-positiva'; $sinal = '+'; }
                                    elseif ($variacao_peso < -0.1) { $classe_var = 'variacao-negativa'; $sinal = ''; }
                                }
                            ?>
                            <div class="valor <?= $classe_var ?>">
                                <?= $variacao_peso !== null
                                    ? $sinal . number_format($variacao_peso, 1, ',', '.') . ' kg'
                                    : '—' ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="historico-container">
                    <div class="historico-header">
                        <h2 class="section-title" style="border: none; padding: 0; margin: 0;">Suas Medidas Salvas</h2>
                        <span class="badge-total"><?= $total_registros ?> registro(s)</span>
                    </div>

                    <?php if ($total_registros > 0): ?>
                        <div class="table-responsive">
                            <table class="historico-table">
                                <thead>
                                    <tr>
                                        <th>Data do Registro</th>
                                        <th>Peso (kg)</th>
                                        <th>Altura (m)</th>
                                        <th>IMC</th>
                                        <th>Classificação</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($linha = $resultado->fetch_assoc()): ?>
                                        <?php
                                            $data_formatada = date("d/m/Y - H:i", strtotime($linha['data_registro']));

                                            $cor_badge = "#2ecc71";
                                            if ($linha['classificacao'] == "Abaixo do peso" || $linha['classificacao'] == "Sobrepeso") $cor_badge = "#f39c12";
                                            if ($linha['classificacao'] == "Obesidade") $cor_badge = "#e74c3c";
                                        ?>
                                        <tr>
                                            <td><?= $data_formatada ?></td>
                                            <td><?= number_format($linha['peso'], 2, ',', '.') ?></td>
                                            <td><?= number_format($linha['altura'], 2, ',', '.') ?></td>
                                            <td><strong><?= number_format($linha['imc'], 2, ',', '.') ?></strong></td>
                                            <td>
                                                <span class="badge" style="background-color: <?= $cor_badge ?>;">
                                                    <?= htmlspecialchars($linha['classificacao']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 50px 20px; color: var(--text-muted);">
                            <div style="font-size: 48px; margin-bottom: 15px;">📊</div>
                            <p>Você ainda não tem nenhuma avaliação salva.</p>
                            <a href="index.php" class="btn-solid" style="display: inline-block; margin-top: 20px;">
                                Calcular meu IMC agora
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>
        </div>
    </main>

    <?php include 'ia/chat_flutuante.php'; ?>

    <script>
        function toggleProfileMenu(event) {
            if (event) { event.stopPropagation(); }
            const menu = document.getElementById('profileMenu');
            if (menu) { menu.classList.toggle('active'); }
        }

        document.addEventListener('click', function(event) {
            const menu = document.getElementById('profileMenu');
            const pic = document.querySelector('.profile-pic');
            if (menu && menu.classList.contains('active')) {
                if (!menu.contains(event.target) && (!pic || !pic.contains(event.target))) {
                    menu.classList.remove('active');
                }
            }
        });
    </script>

</body>
</html>