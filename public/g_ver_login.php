<?php
session_start();
$tries = [
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../../../vendor/autoload.php',
    __DIR__ . '/../../../../vendor/autoload.php',
];

$autoload = null;
foreach ($tries as $p) {
    if (is_readable($p)) { $autoload = $p; break; }
}

if (!$autoload) {
    error_log("Autoload não encontrado. __DIR__=" . __DIR__);
    http_response_code(500);
    exit('Erro: vendor/autoload.php não encontrado. Ajuste o caminho no g_ver_login.php.');
}
require_once $autoload;

// Configurações do Google Client (via variáveis de ambiente)
$clientID = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');
$redirectURI = getenv('GOOGLE_REDIRECT_URI') ?: 'https://reparei.com.br/solicitacao_vistoria/google_oauth_return.php';

if (!$clientID || !$clientSecret) {
    http_response_code(500);
    exit('Configuração OAuth ausente. Defina GOOGLE_CLIENT_ID e GOOGLE_CLIENT_SECRET.');
}

// Cria um novo cliente do Google
$client = new Google\Client();
$client->setClientId($clientID);
$client->setClientSecret($clientSecret);
$client->setRedirectUri($redirectURI);
$client->setScopes(['email', 'profile']);

// Cria o objeto de autenticação OAuth2
$oauth2 = new Google\Service\Oauth2($client);

if (isset($_GET['logout'])) {
    // Remove a sessão e o token de acesso ao fazer logout
    unset($_SESSION['access_token']);
    session_destroy();
    header('Location: index.php');
    exit;
}

if (isset($_GET['code'])) {
    // Troca o código de autorização por um token de acesso
    $client->authenticate($_GET['code']);
    $accessToken = $client->getAccessToken();

    // Armazena o token de acesso na sessão
    $_SESSION['access_token'] = $accessToken;

    // Redireciona o usuário para a página principal
    header('Location: index.php');
    exit;
}

if (isset($_SESSION['access_token']) && !empty($_SESSION['access_token'])) {
    // Define o token de acesso no cliente
    $client->setAccessToken($_SESSION['access_token']);

    // Verifica se o token de acesso expirou
    if ($client->isAccessTokenExpired()) {
        // Se o token de acesso expirou, é necessário obter um novo token
        if ($client->getRefreshToken()) {
            // Atualiza o token de acesso usando o token de atualização
            $client->fetchAccessTokenWithRefreshToken();
            $_SESSION['access_token'] = $client->getAccessToken();
        } else {
            // Não há um token de atualização disponível, é necessário autenticar novamente
            $authURL = $client->createAuthUrl();
            echo '
            <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
            <style>
              /* Estilos para o botão */
              .centered-buttonl {
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
                z-index: 9999 !important;
              }
          
              /* Estilos para o div transparente */
              .overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(0, 0, 0, 0.8);
                z-index: 9998;
              }
            </style>
            
            <script>
                function redirectToGoogle() {
                    window.location.href = \'' . $authURL . '\';
                }
            </script>
            <div class="overlay"></div>
            <div class="centered-buttonl" style="z-index:9999 !important;">
              <button  onclick="redirectToGoogle()" class="btn btn-primary" style="z-index:9999 !important;">Fazer login com o Google</button>
            </div>';
            die();
            exit;
        }
    }

    // Obtém informações do usuário autenticado
    $userInfo = $oauth2->userinfo->get();
    $email = $userInfo->email;
    $name = $userInfo->name;

    $login_user_id = null;
    // Cria a conexão com o banco de dados (credenciais via ambiente)
    date_default_timezone_set('America/Sao_Paulo');
    $servername = getenv('DB_HOST') ?: 'localhost';
    $username = getenv('DB_USER');
    $password = getenv('DB_PASS');
    $dbname = getenv('DB_NAME');
    $port = getenv('DB_PORT') ?: 3306;

    if (!$username || !$password || !$dbname) {
        http_response_code(500);
        exit('Configuração do banco ausente. Defina DB_USER, DB_PASS e DB_NAME.');
    }

    $conn = new mysqli($servername, $username, $password, $dbname, (int)$port);
    $sql = "SELECT * FROM cargos WHERE email LIKE '$email'";
    $result = $conn->query($sql);
      while ($dados_cargo = $result->fetch_assoc()) {
          $login_user_id=$dados_cargo['id'];
      }
    if($login_user_id==null){
      $sqlLog = "INSERT INTO cargos (email, nome, cargo) VALUES ('$email', '$name', 'guest')";
      $resultLog = $conn->query($sqlLog);
      if ($resultLog) {
        // Obtém o último ID inserido
        $login_user_id = $conn->insert_id;
      } 
    }
    // Exibe as informações do usuário
    //echo 'Email: ' . $email . '<br>';
    //echo 'Nome: ' . $name . '<br>';
} else {
    // O usuário não está autenticado, exibe o link de login do Google
    // O usuário não está autenticado, exibe o link de login do Google
    $authURL = $client->createAuthUrl();
    echo '
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <style>
      /* Estilos para o botão */
      .centered-buttonl {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        z-index: 9999 !important;
      }
  
      /* Estilos para o div transparente */
      .overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.8);
        z-index: 9998;
      }
    </style>
    
    <script>
        function redirectToGoogle() {
            window.location.href = \'' . $authURL . '\';
        }
    </script>
    <div class="overlay"></div>
    <div class="centered-buttonl" style="z-index:9999 !important;">
      <button  onclick="redirectToGoogle()" class="btn btn-primary" style="z-index:9999 !important;">Fazer login com o Google</button>
    </div>';
    die();
}
