<?php
session_start();

// Hard gate to narrow attack surface when ModSecurity is in DetectionOnly mode.
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit('Metodo nao permitido.');
}

if (!empty($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] !== '0') {
    http_response_code(413);
    exit('Corpo nao permitido.');
}

$allowedParams = ['code', 'state', 'scope', 'authuser', 'prompt', 'session_state', 'hd'];
foreach ($_GET as $key => $value) {
    if (!in_array($key, $allowedParams, true) || strlen($value) > 2048) {
        http_response_code(400);
        exit('Parametro invalido.');
    }
}

$tries = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
];

$autoload = null;
foreach ($tries as $p) {
    if (is_readable($p)) { $autoload = $p; break; }
}

if (!$autoload) {
    error_log("Autoload nao encontrado. __DIR__=" . __DIR__);
    http_response_code(500);
    exit('Erro interno: autoload ausente.');
}

require_once $autoload;

// Endpoint antigo mantido por compatibilidade; redireciona para o novo caminho
header('Location: google_oauth_return.php?' . $_SERVER['QUERY_STRING']);
exit;
