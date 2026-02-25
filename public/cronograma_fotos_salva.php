<?php
// Conexão com o banco de dados
require_once 'conexao.php';
require_once 'calc_horas.php';
require_once 'g_ver_login.php';
require_once 'ingaia.php';
$solicitante=$email;

//validação
//if(!isset($_POST["contrato_cod"])){die('ERRO: CÓDIGO NAO INFORMADO');}
        
// Verificar a conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// Verificar se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recuperar os dados do formulário
    if(isset($_POST["contrato_cod"])){
        $contrato_cod = addslashes($_POST["contrato_cod"]);
    }else{
        $contrato_cod = '';
    }
    
        //vistoria de entrada
        $tipo_vistoria_id = 3;
        $imovel_disponivel = addslashes($_POST["imovel_disponivel"]);
        $disponibilidade_motivo = addslashes($_POST["disponibilidade_motivo"]);

        //PRAZOS E HORÁRIOS
        if (isset($_POST['prazo_inicio'])) {
            $prazo_inicio_post = addslashes($_POST["prazo_inicio"]); 
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
    
            $horarioConclusao = calcularHorarioConclusao($prazo_inicio, 20);
            $prazo_dinamico = calcularHorarioConclusao($prazo_inicio,16);
        }
        $data_agendamento = NULL;

    //variaveis
    $nome_cliente='';
    $whatsapp='';
    $obs_contato='';   
    $ch_local = addslashes($_POST["ch_local"]);
    $padrao_vistoria = 'digital';
    $ch_qtd_cartao = 0;
    $ch_qtd_tag = 0;
    $ch_qtd_correio = 0;
    $ch_qtd_carrinho = 0;
    $imovel_tamanho_id = 2;
    $imovel_mobiliado = 0;
    $ch_qtd_controle = 0;
    
    $hoje=date('Y-m-d H:i:s');
    $contrato_id=0;
    $aluguel_valor=0;
    $imovel_endereco=addslashes($_POST["imovel_endereco"]);
    $imovel_numero=addslashes($_POST["imovel_numero"]);
    $imovel_complemento=addslashes($_POST["imovel_complemento"]);
    $imovel_bairro=addslashes($_POST["imovel_bairro"]);
    $imovel_cidade=addslashes($_POST["imovel_cidade"]);
    $imovel_uf=addslashes($_POST["imovel_uf"]);
    if(isset($_POST["ch_local_obs"])){
        $ch_local_obs=addslashes($_POST["ch_local_obs"]);
    }else{
        $ch_local_obs='';
    }

    //verifica se imóvel já esta cadastrado
    $sql = "SELECT * FROM agendamentos WHERE contrato_cod = '$contrato_cod' AND tipo_vistoria_id = $tipo_vistoria_id AND deleted_at is NULL AND data_fim is NULL";
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
    $sql = "INSERT INTO agendamentos (contrato_id, tipo_vistoria_id, contrato_cod, solicitante, imovel_disponivel, disponibilidade_motivo, data_agendamento, prazo_inicio, prazo_fim, prazo_dinamico, ch_local, padrao_vistoria, ch_qtd_cartao, ch_qtd_tag, ch_qtd_correio, ch_qtd_carrinho, created_at, aluguel_valor, imovel_endereco, imovel_numero, imovel_complemento, imovel_bairro, imovel_cidade, imovel_uf, imovel_tamanho_id, imovel_mobiliado, ch_qtd_controle, nome_cliente, whatsapp, obs_contato, ch_local_obs)
            VALUES ('$contrato_id', '$tipo_vistoria_id', '$contrato_cod', '$solicitante', '$imovel_disponivel', '$disponibilidade_motivo', '$data_agendamento', '$prazo_inicio', '$horarioConclusao', '$prazo_dinamico', '$ch_local', '$padrao_vistoria', '$ch_qtd_cartao', '$ch_qtd_tag', '$ch_qtd_correio', '$ch_qtd_carrinho', '$hoje', '$aluguel_valor', '$imovel_endereco', '$imovel_numero', '$imovel_complemento', '$imovel_bairro', '$imovel_cidade', '$imovel_uf', '$imovel_tamanho_id', '$imovel_mobiliado', '$ch_qtd_controle', '$nome_cliente', '$whatsapp', '$obs_contato', '$ch_local_obs')";


    if ($conn->query($sql) === TRUE) {
        $idGerado = $conn->insert_id;
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
