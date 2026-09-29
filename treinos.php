<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$nome_usuario = $_SESSION['usuario_nome'] ?? 'Visitante';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Treinos - Monitoramento Corporal</title>
    <link rel="stylesheet" href="css/estilo.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --mc-primary: #0fb9b1;
            --mc-bg-dark: #1e272e;
            --mc-card-bg: #2c3e50;
            --mc-text-light: #f5f6fa;
        }

        .tabs-container {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
            border-bottom: 2px solid rgba(255,255,255,0.1);
            padding-bottom: 10px;
            overflow-x: auto;
        }
        .tabs-container::-webkit-scrollbar { height: 6px; }
        .tabs-container::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }

        .tab-btn {
            background: transparent;
            color: #a4b0be;
            border: none;
            padding: 10px 20px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s;
            white-space: nowrap;
        }
        .tab-btn:hover {
            color: var(--mc-text-light);
            background: rgba(255,255,255,0.05);
        }
        .tab-btn.active {
            color: var(--mc-text-light);
            background: var(--mc-primary);
            box-shadow: 0 4px 15px rgba(15, 185, 177, 0.4);
        }

        .tab-content { display: none; animation: fadeIn 0.4s ease-out; }
        .tab-content.active { display: block; }

        .treino-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }

        .treino-card {
            background: var(--mc-card-bg);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.05);
            display: flex;
            flex-direction: column;
            transition: transform 0.3s ease;
        }
        .treino-card:hover {
            transform: translateY(-5px);
            border-color: rgba(15, 185, 177, 0.3);
        }
        .treino-card h3 {
            color: var(--mc-primary);
            margin-bottom: 5px;
            font-size: 22px;
        }
        .treino-card h4 {
            color: var(--mc-text-light);
            font-size: 15px;
            margin-bottom: 15px;
            font-weight: 500;
        }

        .exercicio-list {
            list-style: none;
            padding: 0;
            margin: 0 0 20px 0;
            flex-grow: 1;
        }
        .exercicio-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            color: #d2dae2;
            font-size: 14px;
        }
        .exercicio-item:last-child { border-bottom: none; }

        .btn-iniciar {
            background: linear-gradient(135deg, #0fb9b1, #0c948e);
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: bold;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        .btn-iniciar:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(15, 185, 177, 0.4);
        }

        .btn-timer-small {
            background: rgba(15, 185, 177, 0.15);
            color: var(--mc-primary);
            border: 1px solid rgba(15, 185, 177, 0.4);
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }
        .btn-timer-small:hover {
            background: var(--mc-primary);
            color: #fff;
        }

        .modal-overlay {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            display: none; justify-content: center; align-items: center;
            z-index: 10000;
            opacity: 0; transition: opacity 0.3s;
        }
        .modal-overlay.active { display: flex; opacity: 1; }

        .timer-box {
            background: rgba(30, 39, 46, 0.95);
            border: 1px solid rgba(15, 185, 177, 0.3);
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.6);
            min-width: 320px;
            transform: scale(0.9);
            transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .modal-overlay.active .timer-box { transform: scale(1); }

        .timer-title { color: var(--mc-text-light); font-size: 18px; margin-bottom: 20px; font-weight: 600; }
        .timer-display {
            font-size: 64px;
            font-weight: 700;
            color: var(--mc-primary);
            font-variant-numeric: tabular-nums;
            margin-bottom: 30px;
            letter-spacing: 2px;
        }

        .timer-controls { display: flex; justify-content: center; gap: 15px; flex-wrap: wrap; }
        .timer-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
            flex: 1;
            min-width: 100px;
        }
        .btn-play { background: var(--mc-primary); color: white; }
        .btn-play:hover { background: #0c948e; }
        .btn-pause { background: #f39c12; color: white; }
        .btn-close { background: #e74c3c; color: white; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .profile-container { position: absolute; top: 20px; right: 40px; display: flex; align-items: center; gap: 12px; z-index: 1000; }
        .profile-pic {
            width: 45px; height: 45px;
            border-radius: 50%;
            background: #0fb9b1;
            color: #1e272e;
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: bold;
            cursor: pointer;
            border: 2px solid transparent;
            transition: 0.3s;
            user-select: none;
        }
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
        .dropdown-header h4 { margin: 0; color: #0fb9b1; }
        .dropdown-header small { color: #808e9b; }

        .dropdown-item {
            padding: 10px 0;
            color: #d2dae2;
            text-decoration: none;
            display: block;
            border-bottom: 1px solid rgba(255,255,255,0.05);
            font-size: 13px;
            transition: 0.2s;
        }
        .dropdown-item:hover { color: #0fb9b1; padding-left: 4px; }

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
        <a href="treinos.php" class="nav-link active">🏋️ Treinos</a>
        <a href="nutricao.php" class="nav-link">🍎 Nutrição</a>
        <a href="configuracoes.php" class="nav-link" style="margin-top: auto;">⚙️ Configurações</a>
    </nav>

    <main class="main-content">
        <header style="position: relative;">
            <div class="header-text">
                <h1>Biblioteca de Treinos</h1>
                <p>Escolha sua rotina. Treinos focados para academia ou calistenia em casa.</p>
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

        <div class="tabs-container">
            <button class="tab-btn active" onclick="abrirAba('aba-superiores', this)">💪 Superiores</button>
            <button class="tab-btn" onclick="abrirAba('aba-inferiores', this)">🦵 Inferiores</button>
            <button class="tab-btn" onclick="abrirAba('aba-cardio', this)">🏃 Cardio & Core</button>
            <button class="tab-btn" onclick="abrirAba('aba-calistenia', this)">🤸 Calistenia em Casa</button>
        </div>

        <div id="aba-superiores" class="tab-content active">
            <div class="treino-grid">
                <div class="treino-card">
                    <h3>Treino A: Peito e Tríceps</h3>
                    <h4>Foco em empurrar (Push)</h4>
                    <ul class="exercicio-list">
                        <li class="exercicio-item"><span>Supino Reto Barra (4x10)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Supino Reto')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Supino Inclinado Halter (3x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Supino Inclinado')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Crucifixo Máquina (3x15)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Crucifixo')">⏱️ 45s</button></li>
                        <li class="exercicio-item"><span>Tríceps Polia Alta (4x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Tríceps Polia')">⏱️ 45s</button></li>
                        <li class="exercicio-item"><span>Tríceps Corda (3x15)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Tríceps Corda')">⏱️ 45s</button></li>
                    </ul>
                    <button class="btn-iniciar" onclick="abrirCronometro(0, 'Treino Peito/Tríceps')">▶ Iniciar Treino</button>
                </div>

                <div class="treino-card">
                    <h3>Treino B: Costas e Bíceps</h3>
                    <h4>Foco em puxar (Pull)</h4>
                    <ul class="exercicio-list">
                        <li class="exercicio-item"><span>Puxada Frontal Aberta (4x10)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Puxada Frontal')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Remada Curvada Barra (4x10)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Remada Curvada')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Remada Baixa Triângulo (3x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Remada Baixa')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Rosca Direta Barra (4x10)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Rosca Direta')">⏱️ 45s</button></li>
                        <li class="exercicio-item"><span>Rosca Martelo Halter (3x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Rosca Martelo')">⏱️ 45s</button></li>
                    </ul>
                    <button class="btn-iniciar" onclick="abrirCronometro(0, 'Treino Costas/Bíceps')">▶ Iniciar Treino</button>
                </div>

                <div class="treino-card">
                    <h3>Treino C: Ombros Isolado</h3>
                    <h4>Desenvolvimento e Postura</h4>
                    <ul class="exercicio-list">
                        <li class="exercicio-item"><span>Desenvolvimento Halter (4x10)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Desenvolvimento')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Elevação Lateral (4x15)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Elevação Lateral')">⏱️ 45s</button></li>
                        <li class="exercicio-item"><span>Elevação Frontal (3x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Elevação Frontal')">⏱️ 45s</button></li>
                        <li class="exercicio-item"><span>Crucifixo Inverso (3x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Crucifixo Inverso')">⏱️ 45s</button></li>
                        <li class="exercicio-item"><span>Encolhimento Trapézio (4x15)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Encolhimento')">⏱️ 45s</button></li>
                    </ul>
                    <button class="btn-iniciar" onclick="abrirCronometro(0, 'Treino Ombros')">▶ Iniciar Treino</button>
                </div>
            </div>
        </div>

        <div id="aba-inferiores" class="tab-content">
            <div class="treino-grid">
                <div class="treino-card">
                    <h3>Foco Quadríceps</h3>
                    <h4>Hipertrofia Anterior</h4>
                    <ul class="exercicio-list">
                        <li class="exercicio-item"><span>Agachamento Livre (4x10)</span> <button class="btn-timer-small" onclick="abrirCronometro(90, 'Agachamento')">⏱️ 90s</button></li>
                        <li class="exercicio-item"><span>Leg Press 45º (4x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(90, 'Leg Press')">⏱️ 90s</button></li>
                        <li class="exercicio-item"><span>Cadeira Extensora (4x15)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Extensora')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Passada/Avanço (3x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Passada')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Panturrilha Máquina (4x20)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Panturrilha')">⏱️ 45s</button></li>
                    </ul>
                    <button class="btn-iniciar" onclick="abrirCronometro(0, 'Treino Quadríceps')">▶ Iniciar Treino</button>
                </div>

                <div class="treino-card">
                    <h3>Foco Posterior e Glúteo</h3>
                    <h4>Força e Estabilização</h4>
                    <ul class="exercicio-list">
                        <li class="exercicio-item"><span>Levantamento Terra/Stiff (4x10)</span> <button class="btn-timer-small" onclick="abrirCronometro(90, 'Stiff')">⏱️ 90s</button></li>
                        <li class="exercicio-item"><span>Mesa Flexora (4x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Mesa Flexora')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Cadeira Flexora (3x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Cadeira Flexora')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Elevação Pélvica (4x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(90, 'Elevação Pélvica')">⏱️ 90s</button></li>
                        <li class="exercicio-item"><span>Cadeira Abdutora (3x15)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Abdutora')">⏱️ 45s</button></li>
                    </ul>
                    <button class="btn-iniciar" onclick="abrirCronometro(0, 'Treino Posterior')">▶ Iniciar Treino</button>
                </div>
            </div>
        </div>

        <div id="aba-cardio" class="tab-content">
            <div class="treino-grid">
                <div class="treino-card">
                    <h3>HIIT Queima de Gordura</h3>
                    <h4>Treino Intervalado (Esteira/Bike)</h4>
                    <ul class="exercicio-list">
                        <li class="exercicio-item"><span>Aquecimento Leve (5 min)</span> <button class="btn-timer-small" onclick="abrirCronometro(300, 'Aquecimento')">⏱️ 5m</button></li>
                        <li class="exercicio-item"><span>Tiro Máximo (10x 30s)</span> <button class="btn-timer-small" onclick="abrirCronometro(30, 'Tiro Máximo')">⏱️ 30s</button></li>
                        <li class="exercicio-item"><span>Descanso Ativo (10x 30s)</span> <button class="btn-timer-small" onclick="abrirCronometro(30, 'Descanso Ativo')">⏱️ 30s</button></li>
                        <li class="exercicio-item"><span>Resfriamento Leve (5 min)</span> <button class="btn-timer-small" onclick="abrirCronometro(300, 'Resfriamento')">⏱️ 5m</button></li>
                    </ul>
                    <button class="btn-iniciar" onclick="abrirCronometro(0, 'HIIT')">▶ Iniciar HIIT</button>
                </div>

                <div class="treino-card">
                    <h3>Core Blindado (Abdômen)</h3>
                    <h4>Estabilidade e Definição</h4>
                    <ul class="exercicio-list">
                        <li class="exercicio-item"><span>Prancha Isométrica (4x Máx)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Prancha')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Abdominal Infra c/ Elevação (4x15)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Infra')">⏱️ 45s</button></li>
                        <li class="exercicio-item"><span>Abdominal Oblíquo (3x20)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Oblíquo')">⏱️ 45s</button></li>
                        <li class="exercicio-item"><span>Roda Abdominal (3x10)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Roda')">⏱️ 60s</button></li>
                    </ul>
                    <button class="btn-iniciar" onclick="abrirCronometro(0, 'Treino Core')">▶ Iniciar Core</button>
                </div>
            </div>
        </div>

        <div id="aba-calistenia" class="tab-content">
            <div class="treino-grid">
                <div class="treino-card">
                    <h3>Iniciante: Corpo Todo</h3>
                    <h4>Foco em Adaptação (Sem Equipamentos)</h4>
                    <ul class="exercicio-list">
                        <li class="exercicio-item"><span>Polichinelos (3x 1 min)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Polichinelo')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Flexão de Braço (Joelho no chão) (3x10)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Flexão Joelho')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Agachamento Livre (3x15)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Agachamento')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Elevação de Quadril Solo (3x15)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Elevação Solo')">⏱️ 45s</button></li>
                        <li class="exercicio-item"><span>Prancha Simples (3x 30s)</span> <button class="btn-timer-small" onclick="abrirCronometro(30, 'Prancha')">⏱️ 30s</button></li>
                    </ul>
                    <button class="btn-iniciar" onclick="abrirCronometro(0, 'Calistenia Iniciante')">▶ Iniciar Iniciante</button>
                </div>

                <div class="treino-card">
                    <h3>Intermediário: Força</h3>
                    <h4>Progressão de Carga Corporal</h4>
                    <ul class="exercicio-list">
                        <li class="exercicio-item"><span>Flexão de Braço Padrão (4x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Flexão')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Mergulho no Banco/Cadeira (4x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Mergulho Banco')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Agachamento Búlgaro (3x10 cada perna)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Búlgaro')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Superman (Lombar no chão) (3x15)</span> <button class="btn-timer-small" onclick="abrirCronometro(45, 'Superman')">⏱️ 45s</button></li>
                        <li class="exercicio-item"><span>Mountain Climbers (3x 40s)</span> <button class="btn-timer-small" onclick="abrirCronometro(40, 'Climbers')">⏱️ 40s</button></li>
                    </ul>
                    <button class="btn-iniciar" onclick="abrirCronometro(0, 'Calistenia Intermediário')">▶ Iniciar Intermediário</button>
                </div>

                <div class="treino-card">
                    <h3>Avançado: Explosão</h3>
                    <h4>Desafio e Resistência (Rua/Parque)</h4>
                    <ul class="exercicio-list">
                        <li class="exercicio-item"><span>Barra Fixa (Pull-ups) (4x Falha)</span> <button class="btn-timer-small" onclick="abrirCronometro(90, 'Barra Fixa')">⏱️ 90s</button></li>
                        <li class="exercicio-item"><span>Flexão Diamante/Declinada (4x12)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Flexão Diamante')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Agachamento c/ Salto (4x15)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Agachamento Salto')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Pistol Squat (Assistido) (3x8)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Pistol Squat')">⏱️ 60s</button></li>
                        <li class="exercicio-item"><span>Burpees (3x 1 min)</span> <button class="btn-timer-small" onclick="abrirCronometro(60, 'Burpees')">⏱️ 60s</button></li>
                    </ul>
                    <button class="btn-iniciar" onclick="abrirCronometro(0, 'Calistenia Avançado')">▶ Iniciar Avançado</button>
                </div>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="cronometroModal">
        <div class="timer-box">
            <h3 class="timer-title" id="timerTitulo">Descanso</h3>
            <div class="timer-display" id="tempoDisplay">00:00</div>

            <div class="timer-controls">
                <button class="timer-btn btn-play" id="btnPlayPause" onclick="toggleTimer()">▶ Iniciar</button>
                <button class="timer-btn btn-pause" onclick="resetTimer()">🔄 Resetar</button>
                <button class="timer-btn btn-close" onclick="fecharCronometro()">✖ Fechar</button>
            </div>
        </div>
    </div>

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

        function abrirAba(idAba, elementoBotao) {
            let conteudos = document.querySelectorAll('.tab-content');
            conteudos.forEach(c => c.classList.remove('active'));

            let botoes = document.querySelectorAll('.tab-btn');
            botoes.forEach(b => b.classList.remove('active'));

            document.getElementById(idAba).classList.add('active');
            elementoBotao.classList.add('active');
        }

        let tempoRestante = 0;
        let tempoInicial = 0;
        let timerIntervalo = null;
        let rodando = false;
        let modoProgressivo = false;

        function atualizarDisplay() {
            let m = Math.floor(tempoRestante / 60).toString().padStart(2, '0');
            let s = (tempoRestante % 60).toString().padStart(2, '0');
            document.getElementById('tempoDisplay').innerText = `${m}:${s}`;
        }

        function abrirCronometro(segundos, titulo) {
            document.getElementById('cronometroModal').classList.add('active');
            document.getElementById('timerTitulo').innerText = titulo ? `Tempo: ${titulo}` : "Cronômetro";

            clearInterval(timerIntervalo);
            rodando = false;
            document.getElementById('btnPlayPause').innerText = "▶ Iniciar";
            document.getElementById('btnPlayPause').style.background = "var(--mc-primary)";

            if (segundos === 0) {
                modoProgressivo = true;
                tempoRestante = 0;
            } else {
                modoProgressivo = false;
                tempoRestante = segundos;
                tempoInicial = segundos;
            }
            atualizarDisplay();
        }

        function fecharCronometro() {
            document.getElementById('cronometroModal').classList.remove('active');
            clearInterval(timerIntervalo);
            rodando = false;
        }

        function toggleTimer() {
            let btn = document.getElementById('btnPlayPause');
            if (rodando) {
                clearInterval(timerIntervalo);
                rodando = false;
                btn.innerText = "▶ Retomar";
                btn.style.background = "var(--mc-primary)";
            } else {
                rodando = true;
                btn.innerText = "⏸ Pausar";
                btn.style.background = "#f39c12";

                timerIntervalo = setInterval(() => {
                    if (modoProgressivo) {
                        tempoRestante++;
                        atualizarDisplay();
                    } else {
                        if (tempoRestante > 0) {
                            tempoRestante--;
                            atualizarDisplay();
                        } else {
                            clearInterval(timerIntervalo);
                            rodando = false;
                            btn.innerText = "▶ Iniciar";
                            btn.style.background = "var(--mc-primary)";
                            alert("Fim do tempo! Prepare-se para a próxima série.");
                        }
                    }
                }, 1000);
            }
        }

        function resetTimer() {
            clearInterval(timerIntervalo);
            rodando = false;
            document.getElementById('btnPlayPause').innerText = "▶ Iniciar";
            document.getElementById('btnPlayPause').style.background = "var(--mc-primary)";

            tempoRestante = modoProgressivo ? 0 : tempoInicial;
            atualizarDisplay();
        }
    </script>
</body>
</html>