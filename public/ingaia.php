<?php
//date_default_timezone_set("Brazil/East");
//header ('Content-type: text/html; charset=UTF-8');  

function superlogicaApi($action,$metodo='GET',$params=array(),$tokens=array()){
    switch ($metodo) {
        case 'POST':
            $contentType = "Content-Type: application/x-www-form-urlencoded";
            break;
        default:
            $contentType = "";
            break;
    }
    $ch = curl_init(); 
    $params = http_build_query($params); 
    curl_setopt($ch, CURLOPT_URL, "https://apps.superlogica.net/imobiliaria/api/".$action.'?'.$params); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo); 
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($contentType,
                                               "app_token: ".$tokens['app_token'],
                                               "access_token: ".$tokens['access_token'],
                                               ));
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response,true);
}
function ingaia($tela, $params){
   //$params['id']=531;
   //$tela='contratos';
 

   
   $oauth = array('access_token'=>'02ql6TNzgeIa','app_token'=>'LwfZRYvrOEwQ');
   $result = superlogicaApi($tela,'GET',$params,array('app_token'=>$oauth['app_token'],'access_token'=>$oauth['access_token']));
   //print_r($result);
   return $result;
}

if(isset($_GET['teste'])){
    $params['id']=531;
	$dados=ingaia('contratos',$params);
	print_r($dados);
}
?>