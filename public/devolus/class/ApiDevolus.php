<?php

class ApiDevolus
{
    private $apiUrl;
    private $token;

    public function __construct($apiUrl)
    {
        $this->apiUrl = $apiUrl;
        $this->token = '85178a16-cf9c-4937-a326-a0cb4999a793';
    }

    public function sendRequest($endpoint, $params = [], $method = 'GET')
    {
        // Cria a URL com os parâmetros
        $url = $this->apiUrl . $endpoint . '?' . http_build_query($params);

        // Inicializa a sessão cURL
        $ch = curl_init();

        // Define as opções da requisição
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->token,
            'User-Agent: Mocambo'
        ]);

        // Verifica o método de requisição
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            // Aqui você pode adicionar o código para configurar os dados do POST, se necessário
        }

        // Executa a requisição
        $response = curl_exec($ch);

        // Verifica se ocorreu algum erro
        if(curl_errno($ch)) {
            echo 'Erro na requisição: ' . curl_error($ch);
        } else {
            // Processa a resposta
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($httpCode === 200) {
                echo 'Requisição bem-sucedida. Resposta da API: ' . $response;
                return $response;
            } else {
                echo 'Erro na requisição. Código HTTP: ' . $httpCode;
                return false;
            }
        }

        // Fecha a sessão cURL
        curl_close($ch);
    }

    public function imovelBusca($codigoExterno)
    {
        $endpoint = 'imoveis';
        $params = [
            'codigoExterno' => $codigoExterno
        ];

        return $this->sendRequest($endpoint, $params, 'GET');
    }

    public function imovelCadastra($data)
    {
        $endpoint = 'imoveis';

        // Aqui você pode adicionar o código para validar e preparar os dados antes de enviar

        return $this->sendRequest($endpoint, $data, 'POST');
    }

    public function locadorBusca($codigoExterno)
    {
        $endpoint = 'locadores/qtd';
        $params = [
            'codigoExterno' => $codigoExterno
        ];

        return $this->sendRequest($endpoint, $params, 'GET');
    }
}

// Configurações da API
$apiUrl = 'https://api.devolusvistoria.com.br/api/';

// Cria uma instância da classe ApiDevolus
$api = new ApiDevolus($apiUrl);




//$locador=$api->locadorBusca('TESTE1');

$imovel=$api->imovelBusca('0307-001');
die();
// Exemplo de uso da função imovelBusca
$imovel=$api->imovelBusca('0307-001');
if($imovel==false){
    //erro
    die('erro na api');
}else if(sizeof($imovel)===0){
    //imovel nao existe, deve ser criado
    // Exemplo de uso da função imovelCadastra
    $data = [
        "id" => 0,
        "codigoExterno" => "string",
        "endereco" => "string",
        "numero" => "string",
        "complemento" => "string",
        "bairro" => "string",
        "cidade" => "string",
        "uf" => "AC",
        "cep" => "string",
        "tipoImovel" => "string",
        "ativo" => true,
        "locadores" => [
            [
                "id" => 0,
                "codigoExterno" => "string",
                "nome" => "string",
                "ativo" => true,
                "cpfCnpj" => "string",
                "estadoCivil" => "string",
                "nacionalidade" => "string",
                "profissao" => "string",
                "telefone" => "string",
                "representante" => "string"
            ]
        ],
        "metragem" => 0
    ];

    //$api->imovelCadastra($data);
}



?>
