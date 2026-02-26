<?php
// Conexão com o banco de dados
require_once 'conexao.php';
require_once 'calc_horas.php';
require_once 'g_ver_login.php';
require_once 'ingaia.php';
require_once 'function_parsedecimal.php';
$solicitante=$email;

//busca imóvel já agendado



//busca imovel superlogica
        if(!isset($_POST["contrato_cod"])){die('ERRO: CÓDIGO NAO INFORMADO');}
        //PESQUISA IMOVEL
        $params['prioridade']='contratos';
        $params['pesquisa']=$_POST["contrato_cod"];
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
            //echo "https://apps.superlogica.net/imobiliaria/api/".$action.'?'.$params;
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
        
        if(sizeof($result['data'])>0){
            $vars['id']=$result['data'][(sizeof($result['data'])-1)]['id_contrato_con'];
            $dados=ingaia('contratos',$vars);
            $SUPERLOGICA['id']=$vars['id'];
            $SUPERLOGICA['codigo']=$dados['data'][0]['codigo_contrato'];
            $ac=$dados['data'][0]['st_areatotal_imo'];
                $ac=parseDecimal($ac);
            $SUPERLOGICA['area_construida']=$ac;

        }else{
            //codigo nao encontrado
            die('ERRO #1: CÓDIGO INVÁLIDO');
        }

        if(strpos($SUPERLOGICA['codigo'], $params['pesquisa'])===false){
            //codigo diferente
            die('ERRO #2: CÓDIGO INVÁLIDO');
        }

        $data_inicio_contrato=DateTime::createFromFormat('m/d/Y', $dados['data'][0]['dt_inicio_con']);
        $data_inicio_contrato=$data_inicio_contrato->format('d/m/Y');

        $dt_comp=explode('/',$data_inicio_contrato);
            $dt_comp=array_reverse($dt_comp);
            $dt_comp=implode($dt_comp)+0;
            $dt_comp_b=date('Ymd')-30;
        $dt_comp_b=date('Ymd')-30;
        $dt_comp_c=date('Ymd')-2;
        if($dt_comp_b>$dt_comp and (!isset($_POST["tipo_vistoria_id"]) or $_POST["tipo_vistoria_id"]==1)){
            //print_r($dados['data'][0]);
            die('ERRO #3: Codigo inválido ou data de contrato incorreta. Encontrado um contrato com inicio em: '.$data_inicio_contrato);
        }else if($_POST["tipo_vistoria_id"]==2 and $dt_comp_c<$dt_comp){
            die('ERRO #4: Codigo inválido ou data de contrato incorreta. Encontrado um contrato com inicio em: '.$data_inicio_contrato);
        }else if($SUPERLOGICA['area_construida']==0){
            die('ERRO #5: Ao que parece a àrea construida aind anão foi cadastrada. Entre em contato com o depto de cadastro e solicite que esta informação seja inserida no Sistema');
        }

        
        //print_r($dados);
        //die();
        //print_r($dados['data'][0]);die();
        $SUPERLOGICA['ENDE']=($dados['data'][0]['st_endereco_imo']);
        $SUPERLOGICA['nume']=($dados['data'][0]['st_numero_imo']);
        $SUPERLOGICA['comp']=$dados['data'][0]['st_complemento_imo'];
        $SUPERLOGICA['BAI']=$dados['data'][0]['st_bairro_imo'];
        $SUPERLOGICA['UF']=$dados['data'][0]['st_estado_imo'];
        $SUPERLOGICA['CEP']=$dados['data'][0]['st_cep_imo'];
        $SUPERLOGICA['MUNICIPIO']=$dados['data'][0]['st_cidade_imo'];
        $SUPERLOGICA['ALUGUEL']=$dados['data'][0]['vl_aluguel_con'];

        $nn=0;
        foreach($dados['data'][0]['proprietarios_beneficiarios'] as $proprietario_at){
            $nn++;
            $proprietarios[$nn]['id_sl']=$proprietario_at['id_pessoa_pes'];
            $proprietarios[$nn]['nome']=$proprietario_at['st_nome_pes'];

            $sql = "SELECT * FROM clientes WHERE id_superlogica = ".$proprietarios[$nn]['id_sl'];
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                // Exibe os dados de cada registro
                while ($row = $result->fetch_assoc()) {
                    $proprietarios[$nn]['id']=$row['id'];

                    //verifica necessidade de atualizar nome
                    if($row['nome']!=$proprietarios[$nn]['nome']){
                        //atualiza nome
                        $sql_up = "UPDATE clientes SET nome = '".addslashes($proprietarios[$nn]['nome'])."' WHERE id = ".$row['id'];
                        if ($conn->query($sql_up) === TRUE) {
                            //echo "Registro atualizado com sucesso.";
                        } else {
                            echo "Erro ao atualizar registro: " . $conn->error;
                            die(0);
                        }
                    }
                }
            }else{
                //salva novo registro
                $sql_ins = "INSERT INTO clientes (nome, id_superlogica)
                VALUES ('".$proprietarios[$nn]['nome']."', '".addslashes($proprietarios[$nn]['id_sl'])."')";

                if ($conn->query($sql_ins) === TRUE) {
                    $proprietarios[$nn]['id']=$conn->insert_id;
                }else{
                    die('erro: ao salvar proprietario');
                }
            }

        }

        $nn=0;
        foreach($dados['data'][0]['inquilinos'] as $inquilino_at){
            $nn++;
            $inquilinos[$nn]['id_sl']=$inquilino_at['id_pessoa_pes'];
            $inquilinos[$nn]['nome']=$inquilino_at['st_nomeinquilino'];

            
            $sql = "SELECT * FROM clientes WHERE id_superlogica = ".$inquilinos[$nn]['id_sl'];
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                // Exibe os dados de cada registro
                while ($row = $result->fetch_assoc()) {
                    $inquilinos[$nn]['id']=$row['id'];

                    //verifica necessidade de atualizar nome
                    if($row['nome']!=$inquilinos[$nn]['nome']){
                        //atualiza nome
                        $sql_up = "UPDATE clientes SET nome = '".addslashes($inquilinos[$nn]['nome'])."' WHERE id = ".$row['id'];
                        if ($conn->query($sql_up) === TRUE) {
                            //echo "Registro atualizado com sucesso.";
                        } else {
                            echo "Erro ao atualizar registro: " . $conn->error;
                            die(0);
                        }
                    }
                }
            }else{
                //salva novo registro
                $sql_ins = "INSERT INTO clientes (nome, id_superlogica)
                VALUES ('".addslashes($inquilinos[$nn]['nome'])."', '".addslashes($inquilinos[$nn]['id_sl'])."')";
                
                if ($conn->query($sql_ins) === TRUE) {
                    $inquilinos[$nn]['id']=$conn->insert_id;
                }else{
                    die('erro: ao salvar inquilino');
                }
            }
        }

        
        $nn=0;
        if(isset($dados['data'][0]['fiadores'])){
            foreach($dados['data'][0]['fiadores'] as $fiador_at){
                $nn++;
                $fiadores[$nn]['id_sl']=$fiador_at['id_pessoa_pes'];
                $fiadores[$nn]['nome']=$fiador_at['st_nomefiador'];

                
                $sql = "SELECT * FROM clientes WHERE id_superlogica = ".$fiadores[$nn]['id_sl'];
                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    // Exibe os dados de cada registro
                    while ($row = $result->fetch_assoc()) {
                        $fiadores[$nn]['id']=$row['id'];

                        //verifica necessidade de atualizar nome
                        if($row['nome']!=$fiadores[$nn]['nome']){
                            //atualiza nome
                            $sql_up = "UPDATE clientes SET nome = '".addslashes($fiadores[$nn]['nome'])."' WHERE id = ".$row['id'];
                            if ($conn->query($sql_up) === TRUE) {
                                //echo "Registro atualizado com sucesso.";
                            } else {
                                echo "Erro ao atualizar registro: " . $conn->error;
                                die(0);
                            }
                        }
                    }
                }else{
                    //salva novo registro
                    $sql_ins = "INSERT INTO clientes (nome, id_superlogica)
                    VALUES ('".$fiadores[$nn]['nome']."', '".addslashes($fiadores[$nn]['id_sl'])."')";

                    if ($conn->query($sql_ins) === TRUE) {
                        $fiadores[$nn]['id']=$conn->insert_id;
                    }else{
                        die('erro: ao salvar fiador');
                    }
                }
            }
        }
        
//fium busca imovel superlogica

// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar os dados do formulário
    $contrato_cod = $_POST["contrato_cod"];
    
    if(!isset($_POST["tipo_vistoria_id"]) or $_POST["tipo_vistoria_id"]==1){
        //vistoria de entrada
        $tipo_vistoria_id = 1;
        $imovel_disponivel = $_POST["imovel_disponivel"];
        $disponibilidade_motivo = $_POST["disponibilidade_motivo"];

        //PRAZOS E HORÁRIOS
        if (isset($_POST['prazo_inicio'])) {
            $prazo_inicio_post = $_POST["prazo_inicio"]; 
            $prazo_inicio_post = DateTime::createFromFormat('Y-m-d', $prazo_inicio_post);
            
            $prazo_inicio = $prazo_inicio_post->format('Y-m-d');
    
            $dtcomp_ini=$prazo_inicio_post->format('Ymd')+0;
            $hjcomp=date('Ymd')+0;
            if($dtcomp_ini<=$hjcomp){
                $prazo_inicio =date('Y-m-d H:i:s');
            }else{
                $prazo_inicio_post->setTime(8, 0, 0);
                $prazo_inicio = $prazo_inicio_post->format('Y-m-d H:i');
            }
    
            $horarioConclusao = calcularHorarioConclusao($prazo_inicio,20); //antes 20
            $prazo_dinamico = calcularHorarioConclusao($prazo_inicio,16); //antes 16
        }
        $data_agendamento = NULL;

        //contato
        $nome_cliente='';
        $whatsapp='';
        $obs_contato='';
    }else{
        //vistoria de saida
        $tipo_vistoria_id = $_POST["tipo_vistoria_id"]+0;
        $imovel_disponivel = 1;
        $disponibilidade_motivo = '';
        
        //PRAZOS E HORÁRIOS
        $data_agendamento = date('Y-m-d H:i', strtotime($_POST["data_agendamento"])); 
        $prazo_inicio=$data_agendamento;
                        
        // Converte a data para o objeto DateTime
        $horarioConclusao = new DateTime($data_agendamento);

        // Adiciona 3 horas à data
        $horarioConclusao->add(new DateInterval('PT3H'));

        // Converte a data de volta para a string formatada
        $horarioConclusao = $horarioConclusao->format('Y-m-d H:i');
        $prazo_dinamico = $horarioConclusao;

        //die($horarioConclusao);
        //contatos
        $nome_cliente= addslashes($_POST["nome_cliente"]); 
        $whatsapp= addslashes(preg_replace('/\D/', '', $_POST["whatsapp"]))+0; 
        $obs_contato= addslashes($_POST["obs_contato"]); 
    }
    
    
    $ch_local = $_POST["ch_local"];
    if(isset($_POST["ch_local_obs"])){
        $ch_local_obs = addslashes($_POST["ch_local_obs"]);
    }else{
        $ch_local_obs = '';
    }
    $padrao_vistoria = $_POST["padrao_vistoria"];
    $ch_qtd_cartao = $_POST["ch_qtd_cartao"];
    $ch_qtd_tag = $_POST["ch_qtd_tag"];
    $ch_qtd_correio = $_POST["ch_qtd_correio"];
    $ch_qtd_carrinho = $_POST["ch_qtd_carrinho"];
    if($SUPERLOGICA['area_construida']>105){
        $imovel_tamanho_id = 3;
    }else if($SUPERLOGICA['area_construida']>65){
        $imovel_tamanho_id = 2;
    }else{
        $imovel_tamanho_id = 1;
    }

    $imovel_mobiliado = $_POST["imovel_mobiliado"];
    $ch_qtd_controle = $_POST["ch_qtd_controle"];
    
    $hoje=date('Y-m-d H:i:s');
    $contrato_id=$SUPERLOGICA['id'];
    $aluguel_valor=$SUPERLOGICA['ALUGUEL'];
    $imovel_endereco=addslashes($SUPERLOGICA['ENDE']);
    $imovel_numero=addslashes($SUPERLOGICA['nume']);
    $imovel_complemento=addslashes($SUPERLOGICA['comp']);
    $imovel_bairro=addslashes($SUPERLOGICA['BAI']);
    $imovel_cidade=addslashes($SUPERLOGICA['MUNICIPIO']);
    $imovel_uf=addslashes($SUPERLOGICA['UF']);
    $area_construida=$SUPERLOGICA['area_construida'];
    if(isset($_POST["descricaopendencias"])){
        $descricaopendencias = addslashes($_POST["descricaopendencias"]);
    }else{
        $descricaopendencias = '';
    }

    //verifica se imóvel já esta cadastrado
    $sql = "SELECT * FROM agendamentos WHERE contrato_id = $contrato_id AND tipo_vistoria_id = $tipo_vistoria_id AND deleted_at is NULL AND (data_fim is NULL OR data_fim  > CURDATE() )";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        //já existe cadastro
        while ($row = $result->fetch_assoc()) {
            $old_sol['id']=$row['id'];
            $old_sol['solicitante']=$row['solicitante'];
            $old_sol['created_at']=date('d/m/Y H:i', strtotime($row['created_at']));
            if($row['data_fim']===NULL){
                $msg_prazo='Prazo: '.date('d/m/Y H:i', strtotime($row['prazo_fim']));
            }else{
                $msg_prazo='Vistoria Concluida em: '.date('d/m/Y H:i', strtotime($row['data_fim']));
            }
            
        }
        include("model_error.php");
        $error_msg='
            <div>já existe uma solicitação para o imóvel '.$contrato_cod.'</div>
            <div>Id: '.$old_sol['id'].'</div>
            <div>Data da solicitação: '.$old_sol['created_at'].'</div>
            <div>'.$msg_prazo.'</div>
        ';
        $error_page=str_replace('<!--error_title-->','SOLICITAÇÃO INVÁLIDA',$error_page);
        $error_page=str_replace('<!--error_msg-->',$error_msg,$error_page);
        die($error_page);
    }

    // Preparar e executar a instrução SQL para inserir os dados no banco
    $sql = "INSERT INTO agendamentos (contrato_id, tipo_vistoria_id, contrato_cod, solicitante, imovel_disponivel, disponibilidade_motivo, data_agendamento, prazo_inicio, prazo_fim, prazo_dinamico, ch_local, padrao_vistoria, ch_qtd_cartao, ch_qtd_tag, ch_qtd_correio, ch_qtd_carrinho, created_at, aluguel_valor, imovel_endereco, imovel_numero, imovel_complemento, imovel_bairro, imovel_cidade, imovel_uf, imovel_tamanho_id, imovel_mobiliado, ch_qtd_controle, nome_cliente, whatsapp, obs_contato, ch_local_obs, descricaopendencias, imovel_area_construida)
            VALUES ('$contrato_id', '$tipo_vistoria_id', '$contrato_cod', '$solicitante', '$imovel_disponivel', '$disponibilidade_motivo', '$data_agendamento', '$prazo_inicio', '$horarioConclusao', '$prazo_dinamico', '$ch_local', '$padrao_vistoria', '$ch_qtd_cartao', '$ch_qtd_tag', '$ch_qtd_correio', '$ch_qtd_carrinho', '$hoje', '$aluguel_valor', '$imovel_endereco', '$imovel_numero', '$imovel_complemento', '$imovel_bairro', '$imovel_cidade', '$imovel_uf', '$imovel_tamanho_id', '$imovel_mobiliado', '$ch_qtd_controle', '$nome_cliente', '$whatsapp', '$obs_contato', '$ch_local_obs', '$descricaopendencias', '$area_construida')";


    if ($conn->query($sql) === TRUE) {
        $idGerado = $conn->insert_id;
        
        //salvar vinculo proprietario
        foreach($proprietarios as $p_at){
            $sql_p = "INSERT INTO pro_agendamento_cliente (id_agendamento, id_cliente) VALUES ($idGerado, ".$p_at['id'].")";
            if ($conn->query($sql_p) === TRUE) {
                //echo 'vinvulo salvo';
            }else{
                echo 'falha ao salvar vinculo';
            }
        }
        //salvar vinculo inquilino
        foreach($inquilinos as $i_at){
            $sql_p = "INSERT INTO inq_agendamento_cliente (id_agendamento, id_cliente) VALUES ($idGerado, ".$i_at['id'].")";
            if ($conn->query($sql_p) === TRUE) {
                //echo 'vinvulo salvo';
            }else{
                echo 'falha ao salvar vinculo';
            }
        }
        //salvar vinculo fiadores
        if(isset($fiadores)){
            foreach($fiadores as $f_at){
                $sql_p = "INSERT INTO fia_agendamento_cliente (id_agendamento, id_cliente) VALUES ($idGerado, ".$f_at['id'].")";
                if ($conn->query($sql_p) === TRUE) {
                    //echo 'vincu salvo';
                }else{
                    echo 'falha ao salvar vinculo';
                }
            }
        }
        

        $urlDestino = "index.php?id=" . urlencode($idGerado);
        header("Location: " . $urlDestino);   
        echo "Salvo com sucesso. id:".$idGerado;
             
    } else {
        echo "Erro ao salvar: " . $conn->error;
    }
}

// Fechar a conexão com o banco de dados
$conn->close();
?>
