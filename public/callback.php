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

$allowedParams = [
    'code', 'state', 'scope', 'authuser', 'prompt', 'session_state', 'hd',
    // error flow params from Google OAuth
    'error', 'error_description', 'error_uri',
    // OpenID Connect issuer param sometimes present
    'iss'
];
foreach ($_GET as $key => $value) {
    if (!in_array($key, $allowedParams, true) || strlen($value) > 2048) {
        http_response_code(400);
        exit('Parametro invalido.');
    }
}

$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') : null;
$envVendor = getenv('COMPOSER_VENDOR_DIR');

$tries = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    $docRoot ? $docRoot . '/../vendor/autoload.php' : null,
    $docRoot ? $docRoot . '/vendor/autoload.php' : null,
    $envVendor ? $envVendor . '/autoload.php' : null,
];

$autoload = null;
foreach ($tries as $p) {
    if ($p && is_readable($p)) { $autoload = $p; break; }
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
