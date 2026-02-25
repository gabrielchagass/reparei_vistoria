<?php

function superlogicaApi($action,$metodo='GET',$params=array()){
    //variaveis de ambiente
    $oauth = array('access_token'=>'02ql6TNzgeIa','app_token'=>'LwfZRYvrOEwQ');
    $tokens=array('app_token'=>$oauth['app_token'],'access_token'=>$oauth['access_token']);
    
    //funções
    switch ($metodo) {
        case 'POST':
            $contentType = "Content-Type: application/x-www-form-urlencoded";
            break;
        default:
            $contentType = "";
            break;
    }

    //requisição
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

    //retorno
    return json_decode($response,true);
}

function buscar_anexos($id){
    $action='arquivos/index';
    $params=array(
        'idContrato' => $id,
        'pagina' => 1
    );
    $anexos=superlogicaApi($action, 'GET', $params);
    //print_r($anexos);
    return $anexos;
}

function buscar_vistoria_arquivos($id){
    $anexos=buscar_anexos($id);
    $vistorias=array();
    foreach($anexos['data'] as $arquivo){
        if(strpos(strtolower($arquivo['st_nome_arq']),'visto') !== false){
            $vistorias[]=$arquivo;
        }
    }
    //print_r($vistorias);
    return $vistorias;
}

function baixa_vistoria($url_download){
    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $url_download); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}
/*
$vistorias=buscar_vistoria_arquivos(1796);
foreach($vistorias as $vistoria){
    $arquivo=baixa_vistoria($vistoria['url_download']);
    
    header("Content-type:application/".$vistoria['st_extensao_arq']);
    echo $arquivo;
}
*/
?>