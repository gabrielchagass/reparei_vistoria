<?php
require_once('conexao_direta.php');

// Captura o corpo da requisição
$conteudoWebhook = file_get_contents('php://input');
$conteudoWebhook = json_decode($conteudoWebhook, true);
if(!isset($conteudoWebhook['latitude'])){die('REQUEST INVÁLIDO');}

//atribui variaveis
$timestamp = date('Y-m-d_H-i-s');
$dados['latitude']=$conteudoWebhook['latitude'];
$dados['longitude']=$conteudoWebhook['longitude'];
$dados['data']=$conteudoWebhook['time'];
if(isset($conteudoWebhook['additional']['driver_id'])){$dados['driver_id']=$conteudoWebhook['additional']['driver_id'];}else{$dados['driver_id']=null;}
if(isset(['additional']['driver_name'])){$dados['driver_name']=$conteudoWebhook['additional']['driver_name'];}else{$dados['driver_name']='';}
$dados['device_id']=$conteudoWebhook['device']['id'];
$dados['name']=$conteudoWebhook['name'];
$dados['address']=$conteudoWebhook['address'];
$dados['geofence']=$conteudoWebhook['geofence'];
$dados['geofence_id']=$conteudoWebhook['geofence_id'];
$dados['Odometro']=0;
$sensores=$conteudoWebhook['sensors'];
foreach($sensores as $sensor){
    if($sensor['name']=='Odometro'){
        $sensor['Odometro']=$sensor;
    }
}
//trata data
    // Data recebida no formato 'YYYY-MM-DD HH:MM:SS'
    $dataRecebida = $dados['data'];
    $dataObj = new DateTime($dataRecebida, new DateTimeZone('UTC'));
    $fusoHorario = new DateTimeZone('America/Sao_Paulo');
    $dataObj->setTimezone($fusoHorario);
    $dados['data']=$dataObj->format('Y-m-d H:i:s');


//trata dados
foreach ($dados as $chave => $valor) {
    $dados[$chave] = addslashes($valor);
}

// Montando as partes da consulta SQL
$colunas = implode(", ", array_keys($dados));
$valores = implode("', '", array_values($dados));
$sql_ins = "INSERT INTO percurso_eventos ($colunas) VALUES ('$valores')";

if ($conn->query($sql_ins) === TRUE) {
    echo $conn->insert_id;
}else{
    die('erro: ao salvar inquilino');
}
?>