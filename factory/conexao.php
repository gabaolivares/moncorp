<?php
require_once __DIR__ . '/env.php';

$host       = env('DB_HOST');
$usuario_bd = env('DB_USER');
$senha_bd   = env('DB_PASS');
$banco      = env('DB_NAME');

if (!$host || !$usuario_bd || !$banco) {
    http_response_code(500);
    die("Erro de configuração: credenciais do banco de dados não encontradas. Verifique o arquivo .env");
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conexao = new mysqli($host, $usuario_bd, $senha_bd, $banco);
    $conexao->set_charset("utf8mb4");
    date_default_timezone_set('America/Sao_Paulo');
    $conexao->query("SET time_zone = '-03:00'");
} catch (mysqli_sql_exception $e) {
    // Em produção, NUNCA exiba $e->getMessage() para o usuário final
    http_response_code(500);
    die("Falha na conexão com o banco de dados. Tente novamente mais tarde.");
}
