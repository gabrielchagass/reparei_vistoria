<?php
require_once('conexao.php');
require_once('calc_horas.php');

// Verificar se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  // Verificar se os dados foram recebidos corretamente
  
    // Extrair os campos do agendamento
    $agendamentoId = $_GET['id'];
    $data_liberacao = date('Y-m-d H:i:s');
            //calcula novo przo
            $prazoFim=calcularHorarioConclusao($data_liberacao, 20);
            $prazo_dinamico = calcularHorarioConclusao($data_liberacao,16);

    // Atualizar o agendamento no banco de dados
    $sql = "UPDATE agendamentos SET solicitacao_bloqueada = 0, imovel_disponivel = 1, prazo_inicio = '$data_liberacao', prazo_fim = '$prazoFim', prazo_dinamico = '$prazo_dinamico' WHERE id = '$agendamentoId' AND data_fim IS NULL";
    if ($conn->query($sql) === TRUE) {
      //atualizar segunda tabela
      $sqlB = "UPDATE agendamento_bloqueios SET data_liberacao = '$data_liberacao' WHERE agendamento_id = '$agendamentoId' AND data_liberacao IS NULL";
      if ($conn->query($sqlB) === TRUE) {
        //sucesso
      }

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
