<?php
require_once('conexao.php');
require_once('calc_horas.php');
require_once('class/emailvistoria.php');



// Verificar se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  //validação
  if(!isset($_GET['id'])){die('dados insuficientes');}
  
  // Verificar se os dados foram recebidos corretamente
  
    // Extrair os campos do agendamento
    $agendamentoId = $_GET['id'];
    $now = date('Y-m-d H:i:s');

    // Atualizar o agendamento no banco de dados
    $sql = "UPDATE agendamentos SET data_assinatura = '$now' WHERE id = '$agendamentoId' AND data_fim IS NOT NULL AND  data_assinatura IS NULL";

    if ($conn->query($sql) === TRUE) {      
      // Envio da resposta de sucesso em formato JSON
      $response = array(
        'success' => true,
        'message' => 'Agendamento atualizado com sucesso.'
      );
      
      header("Location: index.php");
      echo json_encode($response);
    } else {
      // Envio da resposta de erro em formato JSON
      $response = array(
        'success' => false,
        'message' => 'Erro ao atualizar o agendamento: ' . $conn->error
      );
      echo json_encode($response);
    }
} else {
  // Envio da resposta de erro em formato JSON se a requisição não for do tipo POST
  $response = array(
    'success' => false,
    'message' => 'Método não permitido. Apenas requisições GET são aceitas.'
  );
  echo json_encode($response);
}

// Fechar conexão com o banco de dados
$conn->close();

//envia e-mail
$exe=emailvistoria($agendamentoId);
?>
