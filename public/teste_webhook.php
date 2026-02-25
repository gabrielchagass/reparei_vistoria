<?php
$url = 'https://vistoria.reparei.com.br/devolus/WebHookRequest.php';

$payload = '
{ "codigo": "acc7152e-507e-4430-94a0-3e89f6927b29", "evento": "VISTORIA_ENVIADA", "dados": { "id": 1458801, "data": "2023-07-06T19:39:04.743+00:00", "tipoVistoria": "Entrada", "codigoImovel": "1045-003\t", "idAgendamento": 1441377, "situacao": "NOVA" } }
';

$data = json_decode($payload, true);
$payload = json_encode($data);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {
    echo 'Erro ao enviar o webhook: ' . curl_error($ch);
} else {
    echo 'Webhook enviado com sucesso!';
    echo $response;
}

curl_close($ch);



die();
// Dados a serem enviados via POST
$dados = '{
    "codigo":"2bca5dcd-9b07-4e8b-b42e-9f28b0d5fff4",
    "evento":"VISTORIA_ENVIADA",
    "dados":{
        "id":500237,
        "idAgendamento":800237,
        "data":"2021-01-19T16:08:38.486+0000",
        "tipoVistoria":"Captação",
        "codigoImovel":"teste"
    }
}';

// URL de destino para o envio via POST
$urlDestino = 'https://vistoria.reparei.com.br/devolus/WebHookRequest.php';

// Inicializar a sessão curl
$ch = curl_init();

// Configurar as opções da requisição curl
curl_setopt($ch, CURLOPT_URL, $urlDestino);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

// Enviar a requisição curl e obter a resposta
$resposta = curl_exec($ch);
echo $resposta;

// Verificar se houve algum erro durante a requisição
if (curl_errno($ch)) {
    echo 'Erro na requisição: ' . curl_error($ch);
}

// Fechar a sessão curl
curl_close($ch);

?>
