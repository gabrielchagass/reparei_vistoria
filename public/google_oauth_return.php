<?php
session_start();

// Restrict surface: only GET, no body, limited params.
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

$clientID = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');
$redirectURI = getenv('GOOGLE_REDIRECT_URI') ?: 'https://vistoria.reparei.com.br/google_oauth_return.php';

if (!$clientID || !$clientSecret) {
    http_response_code(500);
    exit('Configuração OAuth ausente. Defina GOOGLE_CLIENT_ID e GOOGLE_CLIENT_SECRET.');
}

$client = new Google\Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectURI);
$client->setScopes(['email', 'profile']);

if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $accessToken = $client->getAccessToken();
    $_SESSION['access_token'] = $accessToken;
}

header('Location: index.php');
exit;
?>
