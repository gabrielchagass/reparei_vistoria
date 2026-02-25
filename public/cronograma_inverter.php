<?php
// Conexão com o banco de dados
require_once 'conexao.php';
require_once 'calc_horas.php';
require_once 'g_ver_login.php';
require_once 'ingaia.php';
require_once 'class/agendamentoLog.php';
$solicitante=$email;

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}


//COLETA VARIAVEIS RECEBIDAS
if(!isset($_POST["ocod"])){die('Código não definido');}
if(!isset($_POST["dcod"])){die('Código não definido');}
if(!isset($_POST["motivo"])){die('motivo não definido');}
if(strlen($_POST["motivo"])<3){die('motivo inválido');}
$ocod=$_POST["ocod"];
$dcod=$_POST["dcod"];
$motivo=$_POST["motivo"];


//busca vistorias em andamento
$em_andamento=array();
$em_andamento_detalhes=array();
$sql = "SELECT * FROM cargos WHERE cargo LIKE '%vistoriador%' AND agendamento_id > 0 ORDER BY email ASC";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    
    $nome_at=explode("@",$row['email']);
    $nome_at=$nome_at[0];

    $em_andamento[]=$row['agendamento_id'];
    $em_andamento_detalhes[$row['agendamento_id']]['nome']=$nome_at;
}

//busca primeira vistoria da fila de vistorias de entrada
$proxima=0;
$agora=date('Y-m-d');
$sql = "SELECT * FROM agendamentos WHERE solicitacao_bloqueada = 0 AND deleted_at IS NULL AND data_fim IS NULL AND (tipo_vistoria_id = 1)  AND prazo_inicio <= '$agora' ORDER BY prazo_dinamico ASC LIMIT 1";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $proxima=$row['id'];
}


//busca dados das vistorias a serem alteradas
$AGENDAMENTOS_OLD=array();
$sql = "SELECT * FROM agendamentos WHERE (id = $ocod OR id = $dcod) AND deleted_at IS NULL AND data_fim IS NULL AND (tipo_vistoria_id = 1) ORDER BY prazo_dinamico ASC LIMIT 2";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $AGENDAMENTOS_OLD[$row['id']]=$row;
}


//verificações adicionais
if(in_array($ocod, $em_andamento)){
    http_response_code(400);
    die('Você não pode alterar a prosição de uma vistoria que esta sendo realizada.');}
if(in_array($dcod, $em_andamento)){
    http_response_code(400);
    die('Você não pode alterar a prosição de uma vistoria que esta sendo realizada.');}
if($ocod == $proxima){
    http_response_code(400);
    die('Você não pode alterar a prosição da 1º vistoria da fila.');}
if($dcod == $proxima){
    http_response_code(400);
    die('Você não pode alterar a prosição da 1º vistoria da fila.');}
if(!isset($AGENDAMENTOS_OLD[$ocod])){
    http_response_code(400);
    die('Alteração não permitida');}
if(!isset($AGENDAMENTOS_OLD[$dcod])){
    http_response_code(400);
    die('Alteração não permitida');
}



//REALIZA ALTERAÇÃO
    //ALTERA OCOD
    $sqlUpdate = "UPDATE agendamentos SET 
        prazo_inicio = '{$AGENDAMENTOS_OLD[$dcod]['prazo_inicio']}', 
        prazo_dinamico = '{$AGENDAMENTOS_OLD[$dcod]['prazo_dinamico']}', 
        prazo_fim = '{$AGENDAMENTOS_OLD[$dcod]['prazo_fim']}' 
        WHERE id = $ocod";
    $resultUpdate = $conn->query($sqlUpdate);

    //ALTERA DCODE
    $sqlUpdate = "UPDATE agendamentos SET 
        prazo_inicio = '{$AGENDAMENTOS_OLD[$ocod]['prazo_inicio']}', 
        prazo_dinamico = '{$AGENDAMENTOS_OLD[$ocod]['prazo_dinamico']}', 
        prazo_fim = '{$AGENDAMENTOS_OLD[$ocod]['prazo_fim']}' 
        WHERE id = $dcod";
    $resultUpdate = $conn->query($sqlUpdate);


//busca dados das vistorias ALTERADOS
    $AGENDAMENTOS_NEW=array();
    $sql = "SELECT * FROM agendamentos WHERE (id = $ocod OR id = $dcod) AND deleted_at IS NULL AND data_fim IS NULL AND (tipo_vistoria_id = 1) ORDER BY prazo_dinamico ASC LIMIT 2";
    $result = $conn->query($sql);
    while ($row = $result->fetch_assoc()) {
        $AGENDAMENTOS_NEW[$row['id']]=$row;
    }


//SALVA LOG
//($con, $user, $agendamento_id, $alteracao_txt, $data_antiga, $data_nova)
$log_a=new agendamentoLog($conn, $login_user_id, $ocod, 'Prazo de vistoria invertido com #['.$dcod.']#', $AGENDAMENTOS_OLD[$ocod], $AGENDAMENTOS_NEW[$ocod]);
$log_a->salvar();


$log_b=new agendamentoLog($conn, $login_user_id, $dcod, 'Prazo de vistoria invertido com #['.$ocod.']#', $AGENDAMENTOS_OLD[$dcod], $AGENDAMENTOS_NEW[$dcod]);
$log_b->salvar();



// Fechar a conexão com o banco de dados
$conn->close();
?>
