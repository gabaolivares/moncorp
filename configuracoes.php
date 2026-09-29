<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id'])) {
    header("Location: model/login.php");
    exit();
}

require_once 'factory/conexao.php';

$usuario_id = (int) $_SESSION['usuario_id'];
$mensagem_sucesso = '';
$mensagem_erro    = '';

$sql = "SELECT nome, email, nivel_atividade, objetivo FROM usuarios WHERE id = ? LIMIT 1";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$usuario = $resultado->fetch_assoc();
$stmt->close();

if (!$usuario) {
    session_destroy();
    header("Location: model/login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome      = trim($_POST['nome'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $atividade = trim($_POST['atividade'] ?? '');
    $objetivo  = trim($_POST['objetivo'] ?? '');

    if ($nome === '' || $email === '' || $atividade === '' || $objetivo === '') {
        $mensagem_erro = "Todos os campos são obrigatórios.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem_erro = "E-mail inválido.";
    } else {
        $sql_check = "SELECT id FROM usuarios WHERE email = ? AND id != ? LIMIT 1";
        $stmt_check = $conexao->prepare($sql_check);
        $stmt_check->bind_param("si", $email, $usuario_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $mensagem_erro = "Este e-mail já está em uso por outra conta.";
        } else {
            $sql_up = "UPDATE usuarios SET nome = ?, email = ?, nivel_atividade = ?, objetivo = ? WHERE id = ?";
            $stmt_up = $conexao->prepare($sql_up);
            $stmt_up->bind_param("ssssi", $nome, $email, $atividade, $objetivo, $usuario_id);

            if ($stmt_up->execute()) {
                $_SESSION['usuario_nome'] = $nome;
                $usuario['nome'] = $nome;
                $usuario['email'] = $email;
                $usuario['nivel_atividade'] = $atividade;
                $usuario['objetivo'] = $objetivo;
                $mensagem_sucesso = "Dados atualizados com sucesso!";
            } else {
                $mensagem_erro = "Erro ao salvar as alterações.";
            }
            $stmt_up->close();
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Monitoramento Corporal</title>
    <link rel="stylesheet" href="css/estilo.css">
</head>
<body>

    <nav class="sidebar">
        <h2>MonCorp</h2>
        <a href="index.php" class="nav-link">🧮 Calculadora</a>
        <a href="avaliacao.php" class="nav-link">📏 Avaliação Física</a>
        <a href="treinos.php" class="nav-link">🏋️ Treinos</a>
        <a href="nutricao.php" class="nav-link">🍎 Nutrição</a>
        <a href="configuracoes.php" class="nav-link active" style="margin-top: auto;">⚙️ Configurações</a>
    </nav>

    <main class="main-content">
        <header>
            <div class="header-text">
                <h1>Configurações da Conta</h1>
                <p>Atualize seus dados pessoais e preferências do sistema.</p>
            </div>
        </header>

        <?php if ($mensagem_sucesso): ?>
            <div style="background: rgba(46, 204, 113, 0.15); border: 1px solid #2ecc71; color: #2ecc71; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                ✅ <?= htmlspecialchars($mensagem_sucesso) ?>
            </div>
        <?php endif; ?>

        <?php if ($mensagem_erro): ?>
            <div style="background: rgba(231, 76, 60, 0.15); border: 1px solid #e74c3c; color: #e74c3c; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                ⚠️ <?= htmlspecialchars($mensagem_erro) ?>
            </div>
        <?php endif; ?>

        <h2 class="section-title">Dados Pessoais</h2>
        <form class="form-medidas" method="POST" action="" style="flex-direction: column; align-items: stretch;">
            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <div class="input-group">
                    <label>Nome Completo</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
                </div>
                <div class="input-group">
                    <label>E-mail</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
                </div>
            </div>

            <div style="display: flex; gap: 15px; margin-top: 15px; flex-wrap: wrap;">
                <div class="input-group">
                    <label>Nível de Atividade</label>
                    <select name="atividade" required style="padding: 12px; border-radius: 8px; border: 1px solid var(--bg-input); background-color: var(--bg-input); color: #fff; outline: none;">
                        <option value="sedentario" <?= $usuario['nivel_atividade'] === 'sedentario' ? 'selected' : '' ?>>Sedentário</option>
                        <option value="iniciante" <?= $usuario['nivel_atividade'] === 'iniciante' ? 'selected' : '' ?>>Iniciante</option>
                        <option value="intermediario" <?= $usuario['nivel_atividade'] === 'intermediario' ? 'selected' : '' ?>>Intermediário</option>
                        <option value="avancado" <?= $usuario['nivel_atividade'] === 'avancado' ? 'selected' : '' ?>>Avançado (Foco em Performance)</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Objetivo Atual</label>
                    <select name="objetivo" required style="padding: 12px; border-radius: 8px; border: 1px solid var(--bg-input); background-color: var(--bg-input); color: #fff; outline: none;">
                        <option value="Perder peso" <?= $usuario['objetivo'] === 'Perder peso' ? 'selected' : '' ?>>Perder peso</option>
                        <option value="Ganhar massa" <?= $usuario['objetivo'] === 'Ganhar massa' ? 'selected' : '' ?>>Ganhar massa muscular</option>
                        <option value="Saude" <?= $usuario['objetivo'] === 'Saude' ? 'selected' : '' ?>>Mais saúde / Condicionamento</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn-salvar" style="margin-top: 20px; align-self: flex-start;">Salvar Alterações</button>
        </form>
    </main>

    <?php include 'ia/chat_flutuante.php'; ?>
</body>
</html>