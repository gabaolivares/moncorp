<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/../factory/conexao.php';

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cadastro.php");
    exit();
}

// Sanitiza e valida entradas
$nome      = trim($_POST['nome'] ?? '');
$genero    = trim($_POST['genero'] ?? '');
$email     = trim($_POST['email'] ?? '');
$atividade = trim($_POST['atividade'] ?? '');
$objetivo  = trim($_POST['objetivo'] ?? '');
$senha     = $_POST['senha'] ?? '';

if ($nome === '' || $email === '' || $senha === '' || $genero === '' || $atividade === '' || $objetivo === '') {
    echo "<script>alert('Preencha todos os campos obrigatórios.'); window.location.href = 'cadastro.php';</script>";
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('E-mail inválido.'); window.location.href = 'cadastro.php';</script>";
    exit();
}

if (strlen($senha) < 6) {
    echo "<script>alert('A senha precisa ter pelo menos 6 caracteres.'); window.location.href = 'cadastro.php';</script>";
    exit();
}

$peso   = floatval(str_replace(',', '.', $_POST['peso'] ?? '0'));
$altura = floatval(str_replace(',', '.', $_POST['altura'] ?? '0'));

if ($altura > 3.0) {
    $altura = $altura / 100;
}

if ($peso <= 0 || $peso > 500) {
    echo "<script>alert('Peso inválido.'); window.location.href = 'cadastro.php';</script>";
    exit();
}

if ($altura <= 0 || $altura > 2.5) {
    echo "<script>alert('Altura inválida. Use metros (ex: 1.75) ou centímetros (ex: 175).'); window.location.href = 'cadastro.php';</script>";
    exit();
}

$imc = $peso / ($altura * $altura);
$imc_formatado = number_format($imc, 2, '.', '');

$senha_criptografada = password_hash($senha, PASSWORD_DEFAULT);

$sql_check = "SELECT id FROM usuarios WHERE email = ? LIMIT 1";
$stmt_check = $conexao->prepare($sql_check);
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    $stmt_check->close();
    echo "<script>alert('Este e-mail já está cadastrado. Faça login ou use outro e-mail.'); window.location.href = 'cadastro.php';</script>";
    exit();
}
$stmt_check->close();

$sql = "INSERT INTO usuarios (nome, genero, email, senha, peso, altura, imc, nivel_atividade, objetivo) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexao->prepare($sql);
$stmt->bind_param("ssssddsss", $nome, $genero, $email, $senha_criptografada, $peso, $altura, $imc_formatado, $atividade, $objetivo);

if ($stmt->execute()) {
    $_SESSION['usuario_id']   = $conexao->insert_id;
    $_SESSION['usuario_nome'] = $nome;

    session_regenerate_id(true);

    $frase_secreta = "Olá, IA! Acabei de me cadastrar no sistema. Meu nome é $nome, tenho $peso kg, $altura m, meu IMC é $imc_formatado e sou do gênero $genero. Meu nível de atividade atual é '$atividade' e meu maior objetivo é '$objetivo'. Como meu assistente de saúde do MonCorp, me dê boas-vindas curtas, elabore uma dica de ouro exclusiva para o meu objetivo e pergunte como pode me ajudar a iniciar minha jornada.";
    $_SESSION['primeira_conversa_ia'] = $frase_secreta;

    echo "<script>
            alert('Cadastro realizado com sucesso! Bem-vindo(a) ao sistema.');
            window.location.href = '../index.php';
          </script>";
} else {
    echo "<script>alert('Erro ao cadastrar. Tente novamente.'); window.location.href = 'cadastro.php';</script>";
}

$stmt->close();
$conexao->close();