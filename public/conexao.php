<?php
date_default_timezone_set('America/Sao_Paulo');
require_once 'g_ver_login.php';

$servername = getenv('DB_HOST') ?: 'localhost';
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$dbname = getenv('DB_NAME');
$port = getenv('DB_PORT') ?: 3306;

if (!$username || !$password || !$dbname) {
    http_response_code(500);
    exit('Configuração do banco ausente. Defina DB_USER, DB_PASS e DB_NAME.');
}

// Cria a conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, '', (int)$port);

// Verifica se ocorreu algum erro na conexão
if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

// Cria o banco de dados se ainda não existir
$create_db_sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($create_db_sql) === FALSE) {
    die("Erro ao criar o banco de dados: " . $conn->error);
}

// Seleciona o banco de dados
$conn->select_db($dbname);

// Restante do código...
?>
