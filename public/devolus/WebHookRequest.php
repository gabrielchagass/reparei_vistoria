<?php
include_once('class/vistoria.php');
require_once('../class/emailvistoria.php');


// Obter o payload JSON enviado via webhook
$payload = file_get_contents('php://input');

// Verificar se o payload foi recebido corretamente
if ($payload === false) {
    http_response_code(400); // Bad Request
    echo 'Erro: Payload vazio ou inválido';
    exit();
}


    // Faça o processamento necessário aqui
    // Obter o conteúdo bruto do corpo da requisição
    $dadosJson = file_get_contents('php://input');

    // Decodificar o JSON para um array associativo
    $data  = json_decode($payload, true);
    
    // Verificar se a decodificação do JSON foi bem-sucedida
    if ($data === null) {
        http_response_code(400); // Bad Request
        echo 'Erro: Falha ao decodificar o JSON';
        exit();
    }

    // Processar e manipular os dados recebidos
    $codigo = $data['codigo'];
    $evento = $data['evento'];
    $dados = $data['dados'];
    
    // Exemplo de como acessar os campos do array $dados
    $id = $dados['id'];
    $dataVistoria = $dados['data'];
    $tipoVistoria = $dados['tipoVistoria'];
    $codigoImovel = $dados['codigoImovel'];
    $idVistoria = $dados['id'];
    $idAgendamento = $dados['idAgendamento'];
    $situacao = $dados['situacao'];


    if($evento=="VISTORIA_ENVIADA"){
        //recebimento de vistoria
        $vistoria = new Vistoria($codigoImovel, $idAgendamento, $idVistoria);
        $resultado_id = $vistoria->concluir($dataVistoria);
        
        if($resultado_id!==false){
            http_response_code(200);
            header('Content-Type: application/json');
            echo json_encode(array('message' => 'Requisição bem-sucedida'));
        }else{
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(array('error' => 'Algo deu errado')); 
        }

        //envia e-mail
        emailvistoria($resultado_id);


    }else if($evento=="VISTORIA_ALTERADA"){
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(array('message' => 'Requisição bem-sucedida'));

    }else if($evento=="VISTORIA_CONCLUIDA"){
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(array('message' => 'Requisição bem-sucedida'));

    }else if($evento=="VISTORIA_CANCELADA"){
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(array('message' => 'Requisição bem-sucedida'));

    }else if($evento=="AGENDAMENTO_NOVO"){
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode(array('message' => 'Requisição bem-sucedida'));

    }else{
       // Evento não previsto
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(array('error' => 'Evento não previsto')); 
    }

?>
