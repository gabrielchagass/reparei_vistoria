<?php
session_start();

$docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') : null;
$envVendor = getenv('COMPOSER_VENDOR_DIR');

require_once __DIR__ . '/../env.php';
loadEnv();

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
    http_response_code(500);
    exit('Autoload ausente. Ajuste caminhos em callback_old.php.');
}

require_once $autoload;
// Configurações do Google (use variáveis de ambiente; nunca commit secrets)
$clientID = getenv('GOOGLE_CLIENT_ID');
$clientSecret = getenv('GOOGLE_CLIENT_SECRET');

$scheme = (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']))
    ? $_SERVER['HTTP_X_FORWARDED_PROTO']
    : ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http');
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dynamicRedirect = $scheme . '://' . $host . '/callback.php';

$redirectURI = getenv('GOOGLE_REDIRECT_URI') ?: $dynamicRedirect;

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

/*
if ($client->isAccessTokenExpired()) {
    // O token de acesso expirou, é necessário obter um novo token de acesso

    // Verifique se existe um token de atualização disponível
    if ($client->getRefreshToken()) {
        // Atualize o token de acesso usando o token de atualização
        $client->fetchAccessTokenWithRefreshToken();
        $newAccessToken = $client->getAccessToken();

        // Atualize o token de acesso armazenado na sessão com o novo token de acesso
        $_SESSION['access_token'] = $newAccessToken;

        // Defina o novo token de acesso no cliente
        $client->setAccessToken($newAccessToken);
    } else {
        // Não há um token de atualização disponível, é necessário autenticar novamente
        // Realize o processo de autenticação novamente para obter um novo token de acesso
        // ou redirecione o usuário para a página de autenticação, se aplicável
        if (!isset($_GET['code'])) {
            // Realize o processo de autenticação novamente para obter um novo token de acesso
            // ou redirecione o usuário para a página de autenticação, se aplicável
            // Exemplo: Redirecionar para a página de autenticação do Google
            $authURL = $client->createAuthUrl();
            header('Location: ' . $authURL);
            exit;
        }
    }
}
*/


// Verifica se o código de autorização está presente na URL

//die($_SESSION['access_token'].'|');
if (isset($_GET['code'])) {
    // Troca o código de autorização por um token de acesso
    $client->authenticate($_GET['code']);
    $accessToken = $client->getAccessToken();

    // Armazena o token de acesso para uso posterior
    // Você pode armazená-lo em uma sessão, banco de dados, arquivo, etc.
    $_SESSION['access_token'] = $accessToken;

    // Redireciona o usuário para a página principal ou outra página desejada
    header('Location: index.php');
    exit;
} elseif (isset($_SESSION['access_token']) and !$client->isAccessTokenExpired()) {
    // Se o token de acesso estiver presente na sessão, usa-o para fazer solicitações ao Google

    // Define o token de acesso no cliente
    $client->setAccessToken($_SESSION['access_token']);

    // Exemplo: Obtém informações do usuário autenticado
    $userInfo = $oauth2->userinfo->get();
    $email = $userInfo->email;
    $name = $userInfo->name;

    // Exemplo: Exibe as informações do usuário
    //echo 'Email: ' . $email . '<br>';
    //echo 'Nome: ' . $name . '<br>';
} else {
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
?>
