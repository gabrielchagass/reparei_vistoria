<?php
require_once('conexao.php');
require_once 'g_ver_login.php';
require_once('calc_horas.php');
require_once('class/consultas.php');
require_once('class/devolus.php');



// Verificar se a requisição é do tipo POST
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  //validação
  if(!isset($_GET['motivo']) or !isset($_GET['id'])){die('dados insuficientes');}
  
  // Verificar se os dados foram recebidos corretamente
  
    // Extrair os campos do agendamento
    $agendamentoId = $_GET['id'];
    $motivo_get = $_GET['motivo'];
      if(isset($_GET['obs'])){$obs=addslashes($_GET['obs']);}else{$obs='';}
    if($motivo_get=='chave' or $motivo_get=='reforma' or $motivo_get=='portaria'){$motivo=$motivo_get;}else{ die('motivo inválido');}

    // Atualizar o agendamento no banco de dados
    $sql = "UPDATE agendamentos SET solicitacao_bloqueada = 1 WHERE id = '$agendamentoId' AND data_fim IS NULL";
    if ($conn->query($sql) === TRUE) {
      //atualizar segunda tabela
      $sqlLog = "INSERT INTO agendamento_bloqueios(agendamento_id, motivo, obs) VALUES ($agendamentoId, '$motivo', '$obs')";
      $resultLog = $conn->query($sqlLog);
      
      // Envio da resposta de sucesso em formato JSON
      $response = array(
        'success' => true,
        'message' => 'Agendamento atualizado com sucesso.'
      );
      
      header("Location: index.php");
      include('vistoriador_cancelar_inicio.php');

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
