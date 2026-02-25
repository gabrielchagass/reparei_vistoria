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
$user_vistoria_id=0;
$sql = "SELECT * FROM cargos WHERE email LIKE '$email' ORDER BY email ASC";
$result = $conn->query($sql);
while ($vistoriador_at = $result->fetch_assoc()) {
    $user_id=$vistoriador_at['id'];
    $devolus_user_id=$vistoriador_at['devolus_id'];
    $user_vistoria_id=$vistoriador_at['agendamento_id'];
}
if($devolus_user_id==0 or $devolus_user_id==null){
    die('o usuário precisa  ser associado a um usuário do devolus');
}
//FIM MEU ID


//verifica agendamento
$consulta=new Consulta($conn);
$agendamento=$consulta->agendamentos($vistoria_id);


//validações de segurança
$retornar_tela=0;
if($user_vistoria_id!=null and $user_vistoria_id!=0){
    $retornar_tela=1;
    $erro='<h1>Ops, parece que você deve fazer uma vistoriaantes desta</h1>';
}
if($agendamento['devolus_agendamento_id']!=0){//JÁ EXISTE UM AGENDAMENTO
    $retornar_tela=1;
    $erro='<h1>ERRO: já existe um agendamento para esta vistoria</h1>';
}
if($agendamento['vistoriador']!=$user_id and $agendamento['vistoriador']!=0){//rESERVADO PARA OUTRO VISTORIADOR
    $retornar_tela=1;
    $erro='<h1>ERRO: Esta vistoria esta reservada para outro vvistoriador</h1>';
}

if($retornar_tela==1){
            // URL de destino para o redirecionamento
            $urlDestino = "vistoriador.php";

            echo "<script type='text/javascript'>";

            // Função para atualizar o contador e redirecionar
            echo "function redirecionarEm10Segundos() {";
            echo "    var contador = 10;"; // Inicia o contador em 10
            echo "    var atualizarContador = function() {";
            echo "        if (contador === 0) {";
            echo "            window.location.href = '" . $urlDestino . "';"; // Redireciona após o contador chegar a 0
            echo "        } else {";
            echo "            document.getElementById('contador').textContent = 'Redirecionando em ' + contador + ' segundos...';";
            echo "            contador--;";
            echo "        }";
            echo "    };";
            echo "    setInterval(atualizarContador, 1000);"; // Atualiza o contador a cada segundo
            echo "}";
            
            // Inicia a função quando a página carrega
            echo "window.onload = redirecionarEm10Segundos;";
            echo "</script>";
            echo $erro.'<div id="contador">Redirecionando em 10 segundos...</div>';
            die();
}




//define variaveis
$devolus=new Devolus();

$devolus->setVistoriador_id($devolus_user_id);
$devolus->setTipoVistoria_id($agendamento['tipo_vistoria_id']);

//cria imóvel
$novoImovel=$devolus->CriarImovel($agendamento);
//echo $novoImovel;

//Cria agendamento
if($novoImovel!=false){
    $novoAgendamento=$devolus->CriarAgendamento($agendamento);
    //echo $novoAgendamento;

    if($novoAgendamento!=false){
        $novoAgendamento=json_decode($novoAgendamento, true);
        $agora=date('Y-m-d H:i:s');
        // Consulta SQL para atualizar o agendamento
        //echo "UPDATE agendamentos SET devolus_agendamento_id = '".$novoAgendamento['id']."', vistoriador = '".$user_id."' WHERE id = $vistoria_id";
        $sqlUpdate = "UPDATE agendamentos SET devolus_agendamento_id = '".$novoAgendamento['id']."', data_inicio = '".$agora."', vistoriador = '".$user_id."' WHERE id = $vistoria_id";
        $resultUpdate = $conn->query($sqlUpdate);

        //cadastra vistoria iniciada
        $sqlUpdate = "UPDATE cargos SET agendamento_id = '".$vistoria_id."' WHERE id = $user_id";
        $resultUpdate = $conn->query($sqlUpdate);
        
        // URL de destino para o redirecionamento
        $urlDestino = "vistoriador.php";

        // Imprimindo o código JavaScript para o redirecionamento
        echo "<script type='text/javascript'>";
        echo "window.location.href = '" . $urlDestino . "';";
        echo "</script>";   

        //envia e-mail
        $exe=emailvistoria($vistoria_id);
    }
}

?>