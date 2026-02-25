<?php
require_once('conexao.php');

// Verificar se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // Verificar se os dados foram recebidos corretamente
  
    // Extrair os campos do agendamento
    $agendamentoId = $_GET['id'];
    $dataExclusao = date('Y-m-d H:i:s');

    // Atualizar o agendamento no banco de dados
    $sql = "UPDATE agendamentos SET deleted_at = '$dataExclusao' WHERE id = '$agendamentoId' AND data_fim IS NULL";
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
    'message' => 'Método não permitido. Apenas requisições POST são aceitas.'
  );
  echo json_encode($response);
}

// Fechar conexão com o banco de dados
$conn->close();
?>
