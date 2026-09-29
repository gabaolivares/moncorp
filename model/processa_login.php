<?php
// Inicia a sessão de forma segura
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../factory/conexao.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

if ($email === '' || $senha === '') {
    echo "<script>alert('Preencha e-mail e senha.'); window.location.href = 'login.php';</script>";
    exit();
}

$sql = "SELECT id, nome, senha FROM usuarios WHERE email = ? LIMIT 1";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $usuario = $resultado->fetch_assoc();

    if (password_verify($senha, $usuario['senha'])) {
        // Regenera o ID da sessão para prevenir session fixation
        session_regenerate_id(true);

        $_SESSION['usuario_id']   = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];

        $stmt->close();
        $conexao->close();

        header("Location: ../index.php");
        exit();
    }
}

$stmt->close();
$conexao->close();

echo "<script>alert('E-mail ou senha incorretos.'); window.location.href = 'login.php';</script>";
exit();