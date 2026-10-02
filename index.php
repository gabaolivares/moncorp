<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nome_usuario = $_SESSION['usuario_nome'] ?? 'Visitante';

$imc_calculado = false;
$imc_valor = 0;
$classificacao = "";
$cor = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['peso']) && isset($_POST['altura'])) {

    $peso_str = str_replace(',', '.', $_POST['peso']);
    $altura_str = str_replace(',', '.', $_POST['altura']);

    $peso = floatval($peso_str);
    $altura = floatval($altura_str);

    if ($altura > 3.0) {
        $altura = $altura / 100;
    }

    if ($altura > 0) {
        $imc_valor = $peso / ($altura * $altura);
        $imc_valor = number_format($imc_valor, 2, '.', '');
        $imc_calculado = true;

        if ($imc_valor < 18.5) {
            $classificacao = "Abaixo do peso";
            $cor = "#f39c12";
        } elseif ($imc_valor >= 18.5 && $imc_valor <= 24.9) {
            $classificacao = "Peso normal";
            $cor = "#2ecc71";
        } elseif ($imc_valor >= 25 && $imc_valor <= 29.9) {
            $classificacao = "Sobrepeso";
            $cor = "#f1c40f";
        } else {
            $classificacao = "Obesidade";
            $cor = "#e74c3c";
        }

        if (isset($_SESSION['usuario_id'])) {
            require_once 'factory/conexao.php';

            $id_user = (int) $_SESSION['usuario_id'];
            $sql_historico = "INSERT INTO historico_imc (usuario_id, peso, altura, imc, classificacao) VALUES (?, ?, ?, ?, ?)";

            $stmt = $conexao->prepare($sql_historico);
            if ($stmt) {
                $stmt->bind_param("iddds", $id_user, $peso, $altura, $imc_valor, $classificacao);
                $stmt->execute();
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Monitoramento Corporal</title>
    <link rel="stylesheet" href="css/estilo.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        .hero-imc {
            background: linear-gradient(135deg, rgba(15, 185, 177, 0.12), rgba(44, 62, 80, 0.4));
            border: 1px solid rgba(15, 185, 177, 0.2);
            border-radius: var(--radius-lg);
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        .hero-imc h2 { color: #fff; font-size: 26px; margin-bottom: 8px; }
        .hero-imc p { color: var(--text-muted); font-size: 14px; max-width: 600px; margin: 0 auto; }

        .resultado-imc { position: relative; overflow: hidden; }
        .resultado-imc::before {
            content: ""; position: absolute; top: -50%; right: -20%;
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(15, 185, 177, 0.15), transparent 70%);
            pointer-events: none;
        }

        .imc-badge-classificacao {
            display: inline-block; padding: 8px 20px; border-radius: 20px;
            font-weight: 700; font-size: 16px; margin-top: 10px; color: #fff;
        }

        .resultado-imc .dica-ia {
            background: rgba(15, 185, 177, 0.08);
            border-left: 3px solid var(--primary);
            padding: 12px 16px; margin-top: 20px;
            border-radius: 8px; font-size: 13px; color: var(--text-light);
            text-align: left;
        }

        .form-vertical .btn-salvar { width: 100%; font-size: 16px; padding: 16px; margin-top: 5px; }

        .cadastro-cta {
            display: block; text-align: center; color: var(--text-muted);
            font-size: 13px; margin-top: 10px; font-style: italic;
        }
    </style>
</head>
<body>

    <nav class="sidebar">
        <h2>MonCorp</h2>
        <a href="index.php" class="nav-link active">🧮 Calculadora</a>
        <a href="avaliacao.php" class="nav-link">📏 Avaliação Física</a>
        <a href="treinos.php" class="nav-link">🏋️ Treinos</a>
        <a href="nutricao.php" class="nav-link">🍎 Nutrição</a>
        <a href="configuracoes.php" class="nav-link" style="margin-top: auto;">⚙️ Configurações</a>
    </nav>

    <main class="main-content">
        <header>
            <div class="header-text">
                <h1>Olá, <?= htmlspecialchars($nome_usuario) ?>!</h1>
                <p>Acompanhe seu progresso e mantenha o foco na sua saúde.</p>
            </div>

            <div class="profile-container">
                <?php if (isset($_SESSION['usuario_id'])): ?>
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
                            <a href="avaliacao.php" class="dropdown-item">📊 Ver Histórico</a>
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

        <div class="area-imc">
            <div class="hero-imc">
                <h2>Calculadora de IMC</h2>
                <p>Preencha seus dados abaixo para calcular o Índice de Massa Corporal e receber orientações personalizadas da nossa IA.</p>
            </div>

            <form class="form-vertical" method="POST" action="">
                <div class="input-group">
                    <label>Gênero</label>
                    <select name="genero" required>
                        <option value="" disabled selected>Selecione seu gênero</option>
                        <option value="Feminino" <?= (isset($_POST['genero']) && $_POST['genero'] == 'Feminino') ? 'selected' : ''; ?>>Feminino</option>
                        <option value="Masculino" <?= (isset($_POST['genero']) && $_POST['genero'] == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                        <option value="Masculino" <?= (isset($_POST['genero']) && $_POST['genero'] == 'Outro') ? 'selected' : ''; ?>>Prefiro não informar</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Peso (kg)</label>
                    <input type="number" step="0.1" name="peso" placeholder="Ex: 75.5"
                           value="<?= isset($_POST['peso']) ? htmlspecialchars($_POST['peso']) : ''; ?>" required>
                </div>

                <div class="input-group">
                    <label>Altura (m ou cm)</label>
                    <input type="number" step="0.01" name="altura" placeholder="Ex: 1.75 ou 175"
                           value="<?= isset($_POST['altura']) ? htmlspecialchars($_POST['altura']) : ''; ?>" required>
                </div>

                <div class="input-group">
                    <label>Objetivo</label>
                    <select name="objetivo" required>
                        <option value="" disabled selected>Selecione seu objetivo</option>
                        <option value="Perder Peso" <?= (isset($_POST['objetivo']) && $_POST['objetivo'] == 'Perder Peso') ? 'selected' : ''; ?>>Perder Peso (Emagrecer)</option>
                        <option value="Ganhar Massa Muscular" <?= (isset($_POST['objetivo']) && $_POST['objetivo'] == 'Ganhar Massa Muscular') ? 'selected' : ''; ?>>Ganhar Massa Muscular (Hipertrofia)</option>
                        <option value="Manter o Peso" <?= (isset($_POST['objetivo']) && $_POST['objetivo'] == 'Manter o Peso') ? 'selected' : ''; ?>>Manter o peso</option>
                        <option value="Sair do Sedentarismo" <?= (isset($_POST['objetivo']) && $_POST['objetivo'] == 'Sair do Sedentarismo') ? 'selected' : ''; ?>>Sair do Sedentarismo</option>
                    </select>
                </div>

                <button type="submit" class="btn-salvar">Calcular e Atualizar</button>

                <?php if (!isset($_SESSION['usuario_id'])): ?>
                    <span class="cadastro-cta">
                        💡 Cadastre-se para salvar seu histórico e receber ajuda da IA!
                    </span>
                <?php endif; ?>
            </form>

            <?php if ($imc_calculado): ?>
                <div class="resultado-imc">
                    <h3>Seu IMC Atual</h3>
                    <div class="valor-imc"><?= $imc_valor ?></div>
                    <span class="imc-badge-classificacao" style="background-color: <?= $cor ?>;">
                        <?= htmlspecialchars($classificacao) ?>
                    </span>

                    <div class="dica-ia">
                        🤖 <strong>Dica da IA:</strong>
                        Quer orientações personalizadas sobre esse resultado?
                        Abra o chat flutuante e converse com nossa Inteligência Artificial.
                    </div>
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
