<?php
require_once('conexao.php');
require_once 'g_ver_login.php';
require_once('class/consultas.php');
require_once('class/devolus.php');
require_once('class/emailvistoria.php');

//verificar variaveis
if(!isset($_GET['id'])){die('ERRO: ID NÃO DEFINIDO');}
$vistoria_id=$_GET['id'];

//MEU ID VISTORIADOR
$user_id=0;
$devolus_user_id=0;
$user_agendamento_atual=0;
$sql = "SELECT * FROM cargos WHERE email LIKE '$email' ORDER BY email ASC";
$result = $conn->query($sql);
while ($vistoriador_at = $result->fetch_assoc()) {
    $user_id=$vistoriador_at['id'];
    $devolus_user_id=$vistoriador_at['devolus_id'];
    $user_agendamento_atual=$vistoriador_at['devolus_vistoria_id'];
}

if($devolus_user_id==0 or $devolus_user_id==null){
    die('o usuário precisa  ser associado a um usuário do devolus');
}
//FIM MEU ID


//verifica agendamento
$consulta=new Consulta($conn);
$agendamento=$consulta->agendamentos($vistoria_id);

//define variaveis
$devolus=new Devolus();
$devolus->cancelarAgendamento($agendamento['devolus_agendamento_id']);
//$devolus->setVistoriador_id($devolus_user_id);
//$devolus->setTipoVistoria_id($agendamento['tipo_vistoria_id']);

//echo "UPDATE agendamentos SET devolus_agendamento_id = '".$novoAgendamento['id']."', vistoriador = '".$user_id."' WHERE id = $vistoria_id";
$sqlUpdate = "UPDATE agendamentos SET devolus_agendamento_id = NULL, vistoriador = NULL WHERE id = $vistoria_id";
$resultUpdate = $conn->query($sqlUpdate);

//cadastra vistoria iniciada
$sqlUpdate = "UPDATE cargos SET agendamento_id = 0 WHERE agendamento_id = $vistoria_id";
$resultUpdate = $conn->query($sqlUpdate);

// URL de destino para o redirecionamento
$urlDestino = "index.php";

// Imprimindo o código JavaScript para o redirecionamento
echo "<script type='text/javascript'>";
echo "window.location.href = '" . $urlDestino . "';";
echo "</script>";   

//envia e-mail
$exe=emailvistoria($vistoria_id);

?>
