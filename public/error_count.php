<?php
// Endpoint para contar erros do PHP-FPM com proteção por hash via GET (?h=...)
// Altere o valor de SECRET_HASH para a sua hash única.

define('SECRET_HASH', '4f3c8f6b9a2d4c1e7b5a0d8f2e6c3a1b'); // troque por outro valor único
$provided = $_GET['h'] ?? '';

if (!hash_equals(SECRET_HASH, $provided)) {
    http_response_code(403);
    exit('Acesso negado');
}

$logPath = '/var/log/php-fpm/www-error.log';
if (!is_readable($logPath)) {
    http_response_code(500);
    exit('Log inacessível');
}

// Conta linhas não vazias (cada linha costuma representar um erro/entrada)
$count = 0;
$fh = new SplFileObject($logPath, 'r');
while (!$fh->eof()) {
    $line = $fh->fgets();
    if ($line !== false && trim($line) !== '') {
        $count++;
    }
}

header('Content-Type: application/json');
echo json_encode(['errors' => $count]);
?>
