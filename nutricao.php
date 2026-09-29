<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$logado = isset($_SESSION['usuario_id']);
$dados_usuario = null;

if ($logado) {
    require_once 'factory/conexao.php';
    $usuario_id = (int) $_SESSION['usuario_id'];

    $sql = "SELECT peso, altura, imc, classificacao, data_registro FROM historico_imc WHERE usuario_id = ? ORDER BY data_registro DESC LIMIT 1";
    $stmt = $conexao->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $dados_usuario = $resultado->fetch_assoc();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutrição Personalizada - Monitoramento Corporal</title>
    <link rel="stylesheet" href="css/estilo.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dompurify@3.0.6/dist/purify.min.js"></script>

    <style>
        :root {
            --mc-primary: #0fb9b1;
            --mc-bg-dark: #1e272e;
            --mc-card-bg: #2c3e50;
            --mc-text-light: #f5f6fa;
            --mc-text-muted: #a4b0be;
            --mc-border: rgba(255, 255, 255, 0.08);
        }

        .msg-bloqueio {
            text-align: center;
            background: var(--mc-card-bg);
            padding: 50px;
            border-radius: 12px;
            margin-top: 50px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            border: 1px solid var(--mc-border);
        }
        .msg-bloqueio h2 { color: #ff4757; margin-bottom: 15px; }
        .msg-bloqueio a {
            display: inline-block;
            background: var(--mc-primary);
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            font-weight: bold;
        }

        .macro-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .macro-card {
            background: var(--mc-card-bg);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            border: 1px solid var(--mc-border);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .macro-card h4 { color: var(--mc-text-muted); font-size: 13px; margin-bottom: 8px; text-transform: uppercase; }
        .macro-card .valor { color: var(--mc-text-light); font-size: 26px; font-weight: 700; }
        .macro-card .destaque { color: var(--mc-primary); }

        .ia-nutri-box {
            background: var(--mc-card-bg);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid var(--mc-border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            color: var(--mc-text-light);
            line-height: 1.7;
        }

        .ia-nutri-box h2, .ia-nutri-box h3 { color: var(--mc-primary); margin-top: 20px; margin-bottom: 10px; }
        .ia-nutri-box table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
        .ia-nutri-box th, .ia-nutri-box td { padding: 12px; border: 1px solid var(--mc-border); text-align: left; }
        .ia-nutri-box th { background: rgba(15, 185, 177, 0.2); color: var(--mc-primary); }
        .ia-nutri-box ul { margin-left: 20px; margin-bottom: 15px; }

        @media (max-width: 700px) {
            .ia-nutri-box table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
            .ia-nutri-box { padding: 18px; }
        }

        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
            color: var(--mc-primary);
        }
        .spinner {
            width: 45px; height: 45px;
            border: 4px solid rgba(15, 185, 177, 0.2);
            border-top: 4px solid var(--mc-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        .profile-container { position: absolute; top: 20px; right: 40px; display: flex; align-items: center; gap: 12px; z-index: 1000; }
        .profile-pic { width: 45px; height: 45px; border-radius: 50%; background: var(--mc-primary); color: #1e272e; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: bold; cursor: pointer; border: 2px solid transparent; transition: 0.3s; user-select: none; }
        .profile-pic:hover { border-color: white; transform: scale(1.05); }

        .profile-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 55px;
            width: 240px;
            background: #2c3e50;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.6);
            padding: 15px;
            border: 1px solid #34495e;
        }
        .profile-dropdown.active { display: block; }

        .dropdown-header { border-bottom: 1px solid #34495e; padding-bottom: 8px; margin-bottom: 8px; }
        .dropdown-header h4 { margin: 0; color: var(--mc-primary); }
        .dropdown-header small { color: var(--mc-text-muted); }

        .dropdown-item {
            padding: 10px 0;
            color: #d2dae2;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 13px;
            transition: 0.2s;
        }
        .dropdown-item:hover { color: var(--mc-primary); padding-left: 4px; }

        .btn-logout {
            background: #ff4757;
            color: white;
            border: none;
            padding: 8px;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            margin-top: 12px;
            font-weight: bold;
            transition: 0.2s;
        }
        .btn-logout:hover { background: #ff6b81; }
    </style>
</head>
<body>

    <nav class="sidebar">
        <h2>MonCorp</h2>
        <a href="index.php" class="nav-link">🧮 Calculadora</a>
        <a href="avaliacao.php" class="nav-link">📏 Avaliação Física</a>
        <a href="treinos.php" class="nav-link">🏋️ Treinos</a>
        <a href="nutricao.php" class="nav-link active">🍎 Nutrição</a>
        <a href="configuracoes.php" class="nav-link" style="margin-top: auto;">⚙️ Configurações</a>
    </nav>

    <main class="main-content">
        <header style="position: relative;">
            <div class="header-text">
                <h1>Plano Nutricional com IA</h1>
                <p>Recomendações e dieta geradas dinamicamente com base no seu perfil físico.</p>
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
                            <a href="#" class="dropdown-item">📊 Atualizar Peso/Medidas</a>
                            <a href="#" class="dropdown-item">🎯 Mudar Objetivo Atual</a>
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
                    <p>O seu plano nutricional personalizado é uma funcionalidade exclusiva para membros do MonCorp.</p>
                    <p>Faça login ou cadastre-se para calcular sua dieta com nossa Inteligência Artificial!</p>
                    <a href="model/login.php">Fazer Login</a>
                    <p style="margin-top: 15px;">Não tem uma conta? <a href="model/cadastro.php" style="background: none; color: var(--mc-primary); padding: 0;">Cadastre-se aqui</a></p>
                </div>

            <?php elseif (!$dados_usuario): ?>

                <div class="msg-bloqueio">
                    <h2 style="color: var(--mc-primary);">📊 Nenhuma Avaliação Encontrada</h2>
                    <p>Você precisa registrar o seu Peso e Altura primeiro no Dashboard para podermos calcular seu plano nutricional.</p>
                    <a href="index.php">Ir ao Dashboard e Calcular IMC</a>
                </div>

            <?php else: ?>

                <?php
                    $peso = (float)$dados_usuario['peso'];
                    $altura = (float)$dados_usuario['altura'];
                    $imc = (float)$dados_usuario['imc'];
                    $classificacao = $dados_usuario['classificacao'];
                    $agua_ml = $peso * 35;
                ?>

                <div class="macro-container">
                    <div class="macro-card">
                        <h4>Peso Considerado</h4>
                        <div class="valor destaque"><?= number_format($peso, 1, ',', '.') ?> kg</div>
                    </div>
                    <div class="macro-card">
                        <h4>Classificação IMC</h4>
                        <div class="valor" style="font-size: 20px; color: #f1c40f;"><?= htmlspecialchars($classificacao) ?></div>
                    </div>
                    <div class="macro-card">
                        <h4>Meta Mínima de Água Diária</h4>
                        <div class="valor" style="color: #3498db;"><?= number_format($agua_ml, 0, ',', '.') ?> ml</div>
                    </div>
                </div>

                <div class="ia-nutri-box" id="conteudoDietaIA">
                    <div class="loading-state" id="loadingIA">
                        <div class="spinner"></div>
                        <h3>Consultando a IA do MonCorp...</h3>
                        <p style="color: var(--mc-text-muted);">Montando seu plano de refeições e calculando gramas de proteína, carboidrato e gordura...</p>
                    </div>
                </div>

                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        const promptDiscreto = `Como assistente nutricional do MonCorp, monte um plano alimentar personalizado e estruturado em tabela para um usuário com as seguintes medidas registradas no banco de dados:
                        - Peso: ${<?= json_encode($peso) ?>} kg
                        - Altura: ${<?= json_encode($altura) ?>} m
                        - IMC Atual: ${<?= json_encode($imc) ?>} (${<?= json_encode($classificacao) ?>})

                        Por favor, forneça na sua resposta:
                        1. A quantidade total recomendada em gramas de **Proteínas**, **Carboidratos** e **Gorduras** por dia.
                        2. A meta exata diária de **água em ml**.
                        3. Dicas práticas de alimentação e suplementação para este perfil.
                        4. Uma **Tabela Markdown completa de sugestão de refeições diárias** (Café da Manhã, Almoço, Lanche, Jantar, etc) indicando os alimentos e as porções aproximadas.`;

                        const formData = new FormData();
                        formData.append('pergunta', promptDiscreto);
                        formData.append('discreto', 'true');

                        fetch('ia/chat_flutuante.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            const caixa = document.getElementById('conteudoDietaIA');
                            if (data.resposta) {
                                const htmlBruto = marked.parse(data.resposta);
                                const htmlSeguro = DOMPurify.sanitize(htmlBruto, {
                                    ALLOWED_TAGS: ['p','br','strong','em','ul','ol','li','h1','h2','h3','h4','h5','h6','code','pre','blockquote','table','thead','tbody','tr','th','td','a','hr'],
                                    ALLOWED_ATTR: ['href','title','target','rel']
                                });
                                caixa.innerHTML = htmlSeguro;
                            } else {
                                caixa.innerHTML = `<p style="color: #ff4757;">Não foi possível carregar as recomendações no momento. Tente novamente mais tarde.</p>`;
                            }
                        })
                        .catch(err => {
                            console.error("Erro ao comunicar com a IA:", err);
                            document.getElementById('conteudoDietaIA').innerHTML = `<p style="color: #ff4757;">Erro de conexão com o servidor de IA.</p>`;
                        });
                    });
                </script>

            <?php endif; ?>
        </div>
    </main>

    <?php
        $caminho_chat = __DIR__ . '/ia/chat_flutuante.php';
        if (file_exists($caminho_chat)) {
            include $caminho_chat;
        }
    ?>

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