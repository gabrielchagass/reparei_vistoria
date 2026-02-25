<?php

function emailvistoria($id){
    if ($id === '') {
        http_response_code(400);
        $msg = 'Faltou o parâmetro id.';
    } else {
        $url = 'https://n8n.sispad.com.br/webhook/emailvistoria/?id=' . rawurlencode($id);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                'User-Agent: Reparei' // opcional
            ],
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($response === false || $httpCode >= 400) {
            http_response_code(502);
            $msg = "Falha ao acionar webhook (HTTP {$httpCode}). Erro cURL: {$err}";
        } else {
            $msg = "Webhook acionado com sucesso para id={$id} (HTTP {$httpCode}).";
        }
    }
}

?>