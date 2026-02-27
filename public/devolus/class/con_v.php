<?php
// Conexão usada pelos webhooks Devolus (sem depender de sessão/login)
date_default_timezone_set('America/Sao_Paulo');

// Carrega variáveis do .env (se o ambiente não tiver)
function loadEnvIfNeeded(string $path): void {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        putenv("$name=$value");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
    }
}

// .env fica na raiz do projeto (três níveis acima desta pasta)
loadEnvIfNeeded(__DIR__ . '/../../../.env');

$servername = getenv('DB_HOST') ?: 'localhost';
$username   = getenv('DB_USER');
$password   = getenv('DB_PASS');
$dbname     = getenv('DB_NAME');
$port       = (int)(getenv('DB_PORT') ?: 3306);

if (!$username || !$password || !$dbname) {
    http_response_code(500);
    exit('Configuração do banco ausente. Defina DB_USER, DB_PASS e DB_NAME.');
}

$conn = new mysqli($servername, $username, $password, $dbname, $port);
if ($conn->connect_error) {
    http_response_code(500);
    exit('Falha na conexão: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
// Mantém sessão MySQL em UTC-3 (São Paulo) para alinhar horários com PHP
$conn->query("SET time_zone = '-03:00'");
?>
