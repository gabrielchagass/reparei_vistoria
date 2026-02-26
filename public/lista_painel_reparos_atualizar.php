<?php
require_once('conexao.php');

// Verificar se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Receber os dados do agendamento em formato JSON
  $dadosAgendamento = json_decode(file_get_contents('php://input'), true);

  // Verificar se os dados foram recebidos corretamente
  if ($dadosAgendamento) {
    // Extrair os campos do agendamento
    $agendamentoId = $dadosAgendamento['id'];
    $vistoriador = $dadosAgendamento['vistoriador'];
    $dataAgendamento = $dadosAgendamento['data_agendamento'];
    // Normaliza data: aceita vazio (NULL) ou ISO e converte para MySQL
    if (empty($dataAgendamento)) {
      $dataAgendamentoSql = "NULL";
    } else {
      $dtObj = date_create($dataAgendamento);
      $dataAgendamentoSql = $dtObj ? "'" . $dtObj->format('Y-m-d H:i:s') . "'" : "NULL";
    }

    
    $termoassinado = $dadosAgendamento['termoassinado'];
    $feitopadrao = $dadosAgendamento['feitopadrao'];
    $testesrealizados = $dadosAgendamento['testesrealizados'];
    $descricaopendencias = $dadosAgendamento['descricaopendencias'];

    // Atualizar o agendamento no banco de dados
    $sql = "UPDATE agendamentos SET vistoriador = '$vistoriador', data_agendamento = $dataAgendamentoSql, termoassinado = '$termoassinado', feitopadrao = '$feitopadrao', testesrealizados = '$testesrealizados', descricaopendencias = '$descricaopendencias' WHERE id = '$agendamentoId'";

    if ($conn->query($sql) === TRUE) {
      // Envio da resposta de sucesso em formato JSON
      $response = array(
        'success' => true,
        'message' => 'Agendamento atualizado com sucesso.'
      );
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
    // Envio da resposta de erro em formato JSON se os dados não forem recebidos corretamente
    $response = array(
      'success' => false,
      'message' => 'Erro ao receber os dados do agendamento.'
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
