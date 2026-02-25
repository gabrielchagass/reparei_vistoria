<?php

        //PESQUISA IMOVEL
        $params['prioridade']='contratos';
        $params['pesquisa']='0187-939/';
        $tela='buscaavancada';
        // print_r($params);
        function superlogicaApi_pesquisa($action,$metodo='GET',$params=array(),$tokens=array()){
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
            //echo $response;
            return json_decode($response,true);
        }


        $oauth = array('access_token'=>'02ql6TNzgeIa','app_token'=>'LwfZRYvrOEwQ');
        $result = superlogicaApi_pesquisa($tela,'GET',$params,array('app_token'=>$oauth['app_token'],'access_token'=>$oauth['access_token']));
        
print_r($result);