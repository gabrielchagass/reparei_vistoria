<?php
require_once('conexao.php');
$LABELSN=array('','X');
$PONTOS=array(0,8,10,13);
if(isset($_GET['m_ant'])){
  $m_ant=$_GET['m_ant'];
}else{
  $m_ant=0;
}

function calculaDistancia($latitude1, $longitude1, $latitude2, $longitude2) {
  $raioTerra = 6371; // Raio da Terra em quilômetros

  $latDelta = deg2rad($latitude2 - $latitude1);
  $lonDelta = deg2rad($longitude2 - $longitude1);

  $a = sin($latDelta / 2) * sin($latDelta / 2) +
       cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) *
       sin($lonDelta / 2) * sin($lonDelta / 2);
  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

  $distancia = $raioTerra * $c * 1000;

  return $distancia;
}

function calc_dif($data1, $data2){
    // Convertendo as datas para o formato DateTime
    $dt1 = DateTime::createFromFormat('d/m/Y H:i:s', $data1);
    $dt2 = DateTime::createFromFormat('d/m/Y H:i:s', $data2);

    // Calculando a diferença
    $diferenca = $dt1->diff($dt2);

    // Calculando a diferença total em horas, minutos e segundos
    $dif['h'] = $diferenca->days * 24 + $diferenca->h;
    $dif['m']  = $diferenca->i;
    $dif['s']  = $diferenca->s;

    return $dif;
}

echo '
<style>
.tipo_1{
  color:#00f;
}
.tipo_2{
  color:#f00;
}
.tipo_5{
  color:#f0f;
}
</style>
';


//consulta resultados de quem devem ser retornados
  $user_id='99999';
  $sql = "SELECT * FROM cargos WHERE email LIKE '$email'";
  $result = $conn->query($sql);
  while ($cargos = $result->fetch_assoc()) {
      $user_cargo=$cargos['cargo'];
      $user_id=$cargos['id'];
  }      

  //verifica filtro da consulta
  if(strpos($user_cargo,"vistoriador")!==FALSE){
    //ve resultado dele mesmo
    $filtro_pes='AND vistoriador = '.$user_id;
    $filtro_pes_txt='Vistoriador: '.$name;
  }else if(strpos($user_cargo,"reparos")!==FALSE){
    //ve resultado equipe
    $filtro_pes='';
    $filtro_pes_txt='Vistoriador: TODOS';
  }else{
    $filtro_pes='AND vistoriador = \'Z\'';
    die();
  }
//final da consulta
if($_GET['vistoriador']){
    $vistoriador=$_GET['vistoriador'];
}else{
    $vistoriador='z';
}
if(!$_GET['ano'] or !$_GET['dia'] or !$_GET['mes']){
    $ano=date('Y');
    $mes=date('m');
    $dia=date('d');
}else{
    $ano=$_GET['ano'];
    $mes=$_GET['mes'];
    $dia=$_GET['dia'];
}
$data_ini=date('Y-m-d H:i:s', mktime(0,0,0,$mes,$dia,$ano));
$data_fim=date('Y-m-d H:i:s', mktime(0,0,0,$mes,$dia+1,$ano));

$calc_ini=date('Y-m-d H:i:s', mktime(0,0,0,$mes,$dia,$ano));
$calc_fim=date('Y-m-d H:i:s', mktime(0,0,0,$mes,$dia,$ano));

echo '
<table>
<tr>
    <td>Vistoriador</td>
    <td>Tipo</td>
    <td>Duração</td>
    <td>Local</td>
    <td>Posição</td>
    <td>Inicio</td>
    <td>Final</td>
</tr>';


$local_interesse=array();
$sql = "SELECT * FROM percurso_locais_interesse";
//echo $sql;
$result = $conn->query($sql);
while ($local = $result->fetch_assoc()) {
  $local_interesse[]=$local;
}

$LE=array();
$sql = "SELECT * FROM percurso_eventos WHERE driver_name = '$vistoriador' AND `data` >= '$data_ini' AND `data` <= '$data_fim' ORDER BY `data` ASC";
//echo $sql;
$result = $conn->query($sql);
while ($evento = $result->fetch_assoc()) {
  $LE[]=$evento;
}

foreach($LE as $key => $evento_at){
  $duracao['h']=0;
  $duracao['m']=0;
  $duracao['s']=0;
  $print=0;
  $local='';
  if(strpos($evento_at['name'], "Ligada")!==false or (strpos($evento_at['name'], "ovimento")!==false and strpos($LE[$key-1]['name'], "Desligada")!==false)){
      $calc_ini=date("d/m/Y H:i:s", strtotime($evento_at['data']));
      $fim=0;
      $print=1;
      for($ii=1;($ii+$key)<sizeof($LE) and $fim==0;$ii++){
          if(strpos($LE[$key+$ii]['name'], "Ligada")!=false){
            $prefix_local='Locomoção + Permanencia em: ';
            $calc_fim=date("d/m/Y H:i:s", strtotime($LE[$key+$ii]['data']));
            $duracao=calc_dif($calc_fim, $calc_ini);
            $local=$LE[$key+$ii]['address'];
            $latitude=$LE[$key+$ii]['latitude'];
            $longitude=$LE[$key+$ii]['longitude'];
            $fim=1;            
          }else if(strpos($LE[$key+$ii]['name'], "Desligada")!=false){
            $prefix_local='Locomoveu-se para ';
            $calc_fim=date("d/m/Y H:i:s", strtotime($LE[$key+$ii]['data']));
            $duracao=calc_dif($calc_fim, $calc_ini);
            $local=$LE[$key+$ii]['address'];
            $latitude=$LE[$key+$ii]['latitude'];
            $longitude=$LE[$key+$ii]['longitude'];
            $fim=1;
          }
      }
  }else if(strpos($evento_at['name'], "Desligada")!==false){
    $calc_ini=date("d/m/Y H:i:s", strtotime($evento_at['data']));
    $fim=0;
    $print=1;
    for($ii=1;($ii+$key)<sizeof($LE) and $fim==0;$ii++){
        if(strpos($LE[$key+$ii]['name'], "Desligada")!=false){
          //proximo registro é o final do trajeto
          die('erro de registro perdido');
        }else if(strpos($LE[$key+$ii]['name'], "Ligada")!=false or strpos($LE[$key+$ii]['name'], "ovimento")!=false){
          $prefix_local='';
          $calc_fim=date("d/m/Y H:i:s", strtotime($LE[$key+$ii]['data']));
          $duracao=calc_dif($calc_fim, $calc_ini);
          $local=$LE[$key+$ii]['address'];
          $latitude=$LE[$key+$ii]['latitude'];
          $longitude=$LE[$key+$ii]['longitude'];
          $fim=1;
        }
    }
  }

  $duracao_txt=$duracao['h'].'h'.$duracao['m'].'m'.$duracao['s'].'s';
  $duracao_txt=str_replace('0h0m0s','',$duracao_txt);
  $duracao_txt=str_replace('0h0m','',$duracao_txt);
  $duracao_txt=str_replace('0h','',$duracao_txt);

  if($print==1 and $duracao_txt!=''){
    $local=explode(', Itatiba',$local);
    $local=$local[0];
    $tipo=0;
    //verifica local
    foreach($local_interesse AS $cerca){
      if(calculaDistancia($latitude, $longitude, $cerca['latitude'], $cerca['longitude']) <= $cerca['raio']){
        $local=$cerca['nome'];
        $tipo=$cerca['tipo'];
      }
    }
    
    $calc_ini_formatado=explode(' ',$calc_ini);
    $calc_ini_formatado=$calc_ini_formatado[1];
    $calc_fim_formatado=explode(' ',$calc_fim);
    $calc_fim_formatado=$calc_fim_formatado[1];

    echo '
        <tr class="tipo_'.$tipo.'">
            <td>'.$vistoriador.'</td>
            <td>'.$tipo.'</td>
            <td>'.$duracao_txt.'</td>
            <td>'.$prefix_local.' '.$local.'</td>
            <td>'.$latitude.', '.$longitude.'</td>
            <td>'.$calc_ini_formatado.'</td>
            <td>'.$calc_fim_formatado.'</td>
        </tr>
    ';
  }
}


?>

<table>