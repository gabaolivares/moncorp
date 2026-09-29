<?php
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
              || (($_SERVER['SERVER_PORT'] ?? 80) == 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function exigir_login(string $redirecionar = null): void
{
    if (!isset($_SESSION['usuario_id'])) {
        $redirecionar = $redirecionar ?? '/model/login.php';
        header("Location: $redirecionar");
        exit;
    }
}

function regenerar_sessao(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_campo(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
}

function validar_csrf(): void
{
    $enviado = $_POST['csrf_token'] ?? '';
    $sessao  = $_SESSION['csrf_token'] ?? '';

    if ($sessao === '' || !hash_equals($sessao, $enviado)) {
        http_response_code(403);
        die('Requisição inválida (CSRF).');
    }
}

function e($valor): string
{
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirecionar_seguro(string $destino): void
{
    // Remove esquemas e hosts
    if (preg_match('#^https?://#i', $destino) || strpos($destino, '//') === 0) {
        $destino = '/';
    }
    header("Location: $destino");
    exit;
}