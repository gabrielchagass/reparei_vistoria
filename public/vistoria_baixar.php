<?php
require_once 'conexao.php';
require_once('calc_horas.php');
require_once('class/devolus.php');
if(!isset($_GET['id_devolus']) or !isset($_GET['nome']) or !isset($_GET['foto'])){die('erro, id nao encontrado');}

if($_GET['foto'] ==  "true"){$foto=true;}else{$foto=false;}
$devolus=new Devolus();
$vistoria=$devolus->baixarVistoriaPdf($_GET['id_devolus'],$foto);
if($vistoria){
    $nome = $_GET['nome']; // exemplo
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $nome . '.pdf"');
    header('Content-Length: ' . strlen($vistoria));
    echo $vistoria;
    exit;
}else{
    echo 'Não foi possivel localizar a vistoria';
}
?>