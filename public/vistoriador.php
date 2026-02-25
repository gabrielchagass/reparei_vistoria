<?php
require_once('conexao.php');
require_once 'g_ver_login.php';
require_once('superlogica/superlogica.php');
require_once('func_calc_agendamento.php');
$vistoriador=$email;
$vistoriador_nome=explode('@',$vistoriador);
$vistoriador_nome=ucfirst(strtolower($vistoriador_nome[0]));
$html_card='';

//VARIAVEIS GLOBAIS
$TIPO_VISTORIA=array('','Entrada','Saida','Fotos');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel do Vistoriador</title>
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

  <style>
    .vis1{
      background-color:#99FF99;
      width:150px;
      margin-top:-30px;
      float:right;
      text-align:center;
    }
    .vis2{
      background-color:#FF99FF;
      width:150px;
      margin-top:-30px;
      float:right;
      text-align:center;
    }
    
    .vis3{
      background-color:#F0E68C;
      width:150px;
      margin-top:-30px;
      float:right;
      text-align:center;
    }
    .quadrotempo{
      float:right;
      font-size:10pt;
      color:#777;
    }
    .hragend{
      font-size:10pt;
      text-align: right;
      color:#777;
      margin: 0;
      padding: 0;
    }
    .tempestimado{
      font-size:9pt;
      text-align: right;
      color:#777;
      margin: 0;
      padding: 0;
    }
    .bl1{
      color:#c0c0c0;
      background-color:#f0f0f0;
    }
  </style>
    <script>

        function exibirModalSemChave(itemId) {
            const allowLink = document.getElementById('allowLink');
            allowLink.href = 'agendamento_bloquear.php?motivo=chave&id=' + itemId; // Adicione o ID ao link
            $('#confirmModalBlock').modal('show'); // Mostra o modal
        }
        function exibirModalEmReforma(itemId) {
            const allowLink = document.getElementById('allowLink');
            allowLink.href = 'agendamento_bloquear.php?motivo=reforma&id=' + itemId; // Adicione o ID ao link
            $('#confirmModalBlock').modal('show'); // Mostra o modal
        }
        function exibirModalEmPortaria(itemId) {
            const allowLink = document.getElementById('allowLink');
            allowLink.href = 'agendamento_bloquear.php?motivo=portaria&id=' + itemId; // Adicione o ID ao link
            $('#confirmModalBlock').modal('show'); // Mostra o modal
        }
        function exibirModalAssinar(itemId) {
            const allowLinkAssinar = document.getElementById('allowLinkAssinar');
            allowLinkAssinar.href = 'agendamento_assinar.php?id=' + itemId; // Adicione o ID ao link
            $('#confirmModalAssinar').modal('show'); // Mostra o modal
        }
  </script>
</head>
<body>
  <div class="container">
    
    <div class="row">
      <div class="col-md-6 offset-md-3 mt-4">
        <div class="card">
          <div class="card-body">
            <table>
              <tr>
                <td>
                  <img src="img/avatar.png" style="border-radius:50%;border:solid 2px #777;padding:0;" class="text-center col-md-8" />
                </td>
                <td>
                    <h1 class="text-right mt-4"><?php echo $vistoriador_nome; ?></h1>
                    <h4 class="text-right mt-4">[Disponibilidade]</h4>
                    <h4 class="text-right mt-4">X Pontos</h4>
              
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>
    </div>

    <?php
    $card_content='
    <div class="row">
      <div class="col-md-6 offset-md-3 mt-4">
        <div class="card">
          <div class="card-body bl<!--block-->">
            <div class="vis<!--vtipo-->">Vistoria de <!--tipo_vistoria--></div>
            <h5 class="card-title">
                <!--cod_contrato-->
                <div class="quadrotempo">
                  <p class="hragend"><!--horario_agendamento--></p>
                  <p class="tempestimado">Estimado: <!--tempo_estimado--></p>
                </div>
            
            </h5>
            
            <p class="card-text"><!--prazo--></p>
            <p class="card-text"><!--endereco_completo--></p>
            <p class="card-text">Chaves: <!--ch_local--></p>
            <!-- botao_iniciar -->
            
              <p class="card-text">
              <!--lista_documentos-->
              </p>
          
          </div>
          <div class="card-body verso" style="display:none;">
          <h5 class="card-title"><!--cod_contrato--> Retirar chaves</h5>
          <p class="card-text">Local da Chave: <!--ch_local--></p>
          <p class="card-text">CÓD Chaveir: <!--ch_cod_chaveiro--></p>
          <p class="card-text">Qtd Controle: <!--ch_qtd_controle--></p>
          <p class="card-text">Qtd Cartão: <!--ch_qtd_cartão--></p>
          <p class="card-text">Qtd Tag: <!--ch_qtd_tag--></p>
          <p class="card-text">Qtd Correio: <!--ch_qtd_correio--></p>
          <p class="card-text">Qtd Carrinho: <!--ch_qtd_carrinho--></p>
          <!-- botao_iniciar -->
        
        </div>

        </div>
      </div>
    </div>';
    //lista de vistoriadores
    $VISTORIADORES=array();
    $sql = "SELECT * FROM cargos WHERE cargo LIKE '%vistoriador%' ORDER BY email ASC";
    $result = $conn->query($sql);
    while ($vistoriador_at = $result->fetch_assoc()) {
      $nome_at=explode("@",$vistoriador_at['email']);
      $nome_at=$nome_at[0];
      $VISTORIADORES[$vistoriador_at['id']]=$nome_at;
    }
    //fim da lista

    //MEU ID VISTORIADOR
    $user_id=0;
    $user_vistoria_id=0;
    $sql = "SELECT * FROM cargos WHERE email LIKE '$email' ORDER BY email ASC";
    $result = $conn->query($sql);
    while ($vistoriador_at = $result->fetch_assoc()) {
      $user_id=$vistoriador_at['id'];
      $user_vistoria_id=$vistoriador_at['agendamento_id'];
    }
    //$user_id=5;
    //FIM MEU ID


    //LISTA VISTORIAS PENDENTES DE ASSINATURA
    $sqlp = "SELECT * FROM agendamentos WHERE deleted_at IS NULL AND data_fim IS NOT NULL AND data_assinatura IS NULL AND vistoriador = $user_id ORDER BY data_fim ASC";
    $resultp = $conn->query($sqlp);

    // Verificar se há registros retornados
    if ($resultp->num_rows > 0) {
      // Loop pelos agendamentos
      echo '
        <div class="container">
          <div class="row">
            <div class="col-12 text-center mt-4">
              <h4><strong>CONFERÊNCIA E ASSINATURA</strong></h4>
            </div>
          </div>
            
          <!-- Cards lado a lado -->
          <div class="row justify-content-center mt-4">
          ';
      while ($agendamento = $resultp->fetch_assoc()) {
        //variaveis adicionais        
        
        $endereco_completo=$agendamento['imovel_endereco'].', '.$agendamento['imovel_numero'].' - '.$agendamento['imovel_complemento'].' - '.$agendamento['imovel_bairro'].' - '.$agendamento['imovel_cidade'].' - '.$agendamento['imovel_uf'].' ('.$agendamento['imovel_condominio'].')';
        $endereco_completo=str_replace('-  -','',$endereco_completo);
        $endereco_completo=str_replace('()','',$endereco_completo);

        echo '
                <div class="col-md-3 mx-2">
                  <div class="card">
                    <div class="card-body">
                      <h5 class="card-title">
                          '.$agendamento['contrato_cod'].'
                      
                      </h5>
                      <div class="ass"><small class="fw-bold">Vistoria de '.$TIPO_VISTORIA[$agendamento['tipo_vistoria_id']].'</small></div>
                      <div class="ass"><small class="fw-bold">Conclusão: '.date("d/m/Y H:i",strtotime($agendamento['data_fim'])).'</small></div>
                      <p class="card-text">'.$endereco_completo.'</p>
                      <button type="button"  onclick="exibirModalAssinar(' . $agendamento['id'] . ')" class="btn btn-warning assinar-agendamento" data-id="'.$agendamento['id'].'">Assinar como Concluido</button>
                    
                    </div>
                  </div>
                </div>
              ';
      }
      echo '</div></div>';
    }

    //FIM LISTA ASSINATURA


    //TESTE
    //2025-02-19 17:51:12

    //libera vistoria de fotos de captação
    $fil_vistoria_foto='';
    if($user_id==5){
      $fil_vistoria_foto='OR tipo_vistoria_id = 3';
    }

    //filtro baseado em vistoria iniciada
    $filtro_vistoria_iniciada='';
    if($user_vistoria_id!=0 and $user_vistoria_id!=null){
      $filtro_vistoria_iniciada='AND (id = '.$user_vistoria_id.' OR vistoriador = '.$user_id.')';
    }

    $amanha=date('Y-m-d H:i:s', mktime(0,0,0,date('m'),(date('d')+2),date('Y')));
    $vistoria_entrada_indicada=0;
    $sql = "SELECT * FROM agendamentos WHERE solicitacao_bloqueada = 0 AND deleted_at IS NULL AND data_fim IS NULL AND (tipo_vistoria_id = 1 ".$fil_vistoria_foto." OR (tipo_vistoria_id = 2 AND data_agendamento < '$amanha')) ".$filtro_vistoria_iniciada." ORDER BY prazo_dinamico ASC";
    //echo $sql;
    $result = $conn->query($sql);

    // Verificar se há registros retornados
    if ($result->num_rows > 0) {
      // Loop pelos agendamentos
      
      echo '
        <div class="container">
        <div class="row">
          <div class="col-md-6 offset-md-3 mt-4">
                      <b>VISTORIAS</b> 
          </div>
        </div>';
      while ($agendamento = $result->fetch_assoc()) {
        //variaveis adicionais
        $endereco_completo=$agendamento['imovel_endereco'].', '.$agendamento['imovel_numero'].' - '.$agendamento['imovel_complemento'].' - '.$agendamento['imovel_bairro'].' - '.$agendamento['imovel_cidade'].' - '.$agendamento['imovel_uf'].' ('.$agendamento['imovel_condominio'].')';
        $endereco_completo=str_replace('-  -','',$endereco_completo);
        $endereco_completo=str_replace('()','',$endereco_completo);

        //campos
        $card_at=$card_content;
        $campos=array('id', 'contrato_id', 'contrato_cod', 'tipo_vistoria_id', 'status_id', 'solicitante', 'aluguel_valor', 'imovel_endereco', 'imovel_numero', 'imovel_complemento', 'imovel_bairro', 'imovel_cidade', 'imovel_uf', 'imovel_condominio', 'imovel_tamanho_id', 'imovel_mobiliado', 'data_agendamento', 'prazo_inicio', 'prazo_fim', 'prazo_dinamico', 'created_at', 'updated_at', 'vistoriador', 'data_fim', 'duracao', 'data_inicio', 'imovel_disponivel', 'disponibilidade_motivo', 'ch_qtd_controle', 'ch_local', 'ch_cod_chaveiro', 'ch_qtd_cartao', 'ch_qtd_tag', 'ch_qtd_correio', 'ch_qtd_carrinho', 'padrao_vistoria', 'nome_cliente', 'whatsapp', 'obs_contato', 'deleted_at', 'termoassinado', 'feitopadrao', 'testesrealizados', 'descricaopendencias', 'imovel_area_construida');
        //foreach($campos as $campoat){
        //  $card_at=str_replace('<!--'.$campoat.'-->',$agendamento[$campoat],$card_at);
        //}

        //busca arquivos da vistoria
        $lista_documentos='';
        if($agendamento['tipo_vistoria_id']==2){
          $vistorias_lista=buscar_vistoria_arquivos($agendamento['contrato_id']);
          $lista_documentos.='
          <details>
          <summary>Documentos:</summary>
          <p>';
          foreach($vistorias_lista as $vistoria){
            $lista_documentos.='<li><a href="'.$vistoria['url_download'].'">'.$vistoria['st_nome_arq'].'</a></li>';
          }
          $hash_at=md5('en'.date('Y').$agendamento['contrato_id'].date('d').'sec'.date('m'));
          $lista_documentos.='<li><a href="https://n8n.sispad.com.br/webhook/TermoEntregaChaves?idc='.$agendamento['contrato_id'].'&hash='.$hash_at.'">Termo de entraga de chaves</a></li>';
          $lista_documentos.='<li><a href="http://192.168.2.3/reparei/conf_doc/faz_arquivo.php?idc='.$agendamento['contrato_id'].'">chaveiro</a></li>';
          
          $lista_documentos.='
          </p>
          <p>&nbspc</p>
          <table>
          <tr>
            <td>Nome:</td>
            <td>'.$agendamento['nome_cliente'].'</td>
          </tr>
          <tr>
            <td>WhatsApp:</td>
            <td>'.$agendamento['whatsapp'].'</td>
          </tr>
          <tr>
            <td>Observações:</td>
            <td>'.$agendamento['obs_contato'].'</td>
          </tr>
        </table>
          </details>';

          
          $card_at=str_replace('<!--prazo_fim-->Agendado para: ',date("d/m/Y H:i",strtotime($agendamento['data_agendamento'])),$card_at);
        }else{
          $card_at=str_replace('<!--prazo_fim-->Prazo: ',date("d/m/Y H:i",strtotime($agendamento['prazo_dinamico'])),$card_at);
        }

        //calcula horario estimado

        $tempo_estimado=round($agendamento['imovel_area_construida']*0.6);
        if($tempo_estimado==0){
          $tempo_estimado_txt='indisponivel';
        }else if($tempo_estimado<=60){
          $tempo_estimado_txt=$tempo_estimado.' min.';
        }else if($tempo_estimado>60){
          $tempo_em_horas=floor($tempo_estimado/60);
          $tempo_em_minutos=round($tempo_estimado-($tempo_em_horas*60));
          $tempo_estimado_txt=$tempo_em_horas.'h '.$tempo_em_minutos.' min.';
        }

        //calcula horario agendamento
        $txt_agendamento=calc_agendamento($agendamento['data_agendamento'],$agendamento['prazo_inicio'],$agendamento['prazo_fim'],$agendamento['prazo_dinamico']);
        $ag_block=$txt_agendamento['ag_block'];
        $agendamento_at=$txt_agendamento['agendamento_at'];
        $agendamento_dinamico=$txt_agendamento['agendamento_dinamico'];
        $vistoria_agendada=$txt_agendamento['vistoria_agendada'];

        //bloqueia agendamento nao prioritario        
        $ocultar=0;

        if(
          $agendamento['tipo_vistoria_id'] == 1 //vistoria de entrada
          and $vistoria_agendada == false //nao agendada
        ){
          
          $prazo_ini_calc=date("Ymd",strtotime($agendamento['prazo_inicio']))+0;
          $dt_hoje=date("Ymd")+0;

          if(
            (
              $vistoria_entrada_indicada==0 //ainda nao teve um prioritario
              AND ((!$agendamento['vistoriador']) or ($agendamento['vistoriador'] == $user_id)) //não pode estar reservada para outro vistoriador
              and (!$agendamento['devolus_agendamento_id'])
              and $dt_hoje >= $prazo_ini_calc
              and (!$user_vistoria_id)
            ) or (
              $user_vistoria_id >0
              and $user_vistoria_id == $agendamento['id']
            )
          ){
            //agendamento prioritario
            $ag_block=0;
            $vistoria_entrada_indicada=1;
            //echo 's...'.$agendamento['id'];
          }else{
            $ocultar=1; //ocultar vistoria
            $ag_block=1; //bloquear vistoria
            //echo 'n'.$agendamento['id'].'(VIS:'.$agendamento['vistoriador'].'|UID:'.$user_id.'|dev:)';
          }
        }
        

        
        if($ocultar==1){
          $card_at='';
        } else if($ag_block==1){
          //sem botão
          $card_at=str_replace('<!-- botao_iniciar -->','<b>Disponível em Breve</b>', $card_at);
        }else if($agendamento['id'] == $user_vistoria_id){
          $card_at=str_replace('<!-- botao_iniciar -->','
          

          <b>CONFIRA AS CHAVES:</b>
            <table>
            <tr>
              <td>Qtd de Chaves:</td>
              <td>--</td>
            </tr>
            <tr>
              <td>Qtd de Controles:</td>
              <td>'.$agendamento['ch_qtd_controle'].'</td>
            </tr>
              <tr>
                <td>Qtd Cartão de acesso:</td>
                <td>'.$agendamento['ch_qtd_cartao'].'</td>
             </tr>
                <tr>
                  <td>Qtd Tags:</td>
                  <td>'.$agendamento['ch_qtd_tag'].'</td>
                </tr>
                <tr>
                  <td>Qtd Chave Correio:</td>
                  <td>'.$agendamento['ch_qtd_correio'].'</td>
                </tr>
                <tr>
                  <td>Qtd chave carrinho:</td>
                  <td>'.$agendamento['ch_qtd_carrinho'].'</td>
                </tr>
              </table>
              <div><b>&nbsp;</b></div>
              <b>VISTORIA INICIADA</b>
            <table>
              <tr>
                <td>solicitante:</td>
                <td>@' . array_shift(explode('@',$agendamento['solicitante'])). '</td>
              </tr>
              <tr>
                <td>Documento:</td>
                <td>'.$agendamento['padrao_vistoria'].'</td>
              </tr>
              <tr>
                <td>Nome:</td>
                <td>'.$agendamento['nome_cliente'].'</td>
              </tr>
              <tr>
                <td>WhatsApp:</td>
                <td>'.$agendamento['whatsapp'].'</td>
              </tr>
              <tr>
                <td>Observações:</td>
                <td>'.$agendamento['obs_contato'].'</td>
              </tr>
            </table>

              <div><b>&nbsp;</b></div>
          <b>OUTRAS OPÇÕES:</b>
          <table>
            <tr>
              <td><button type="button"  onclick="exibirModalSemChave(' . $agendamento['id'] . ')" class="btn btn-warning bloquear-agendamento" data-id="'.$agendamento['id'].'">Chave indisponivel</button></td>
              <td><button type="button"  onclick="exibirModalEmReforma(' . $agendamento['id'] . ')" class="btn btn-danger bloquear-agendamento" data-id="'.$agendamento['id'].'">Imóvel em Reforma</button></td>
              <td><button type="button"  onclick="exibirModalEmPortaria(' . $agendamento['id'] . ')" class="btn btn-danger bloquear-agendamento" data-id="'.$agendamento['id'].'">Sem autorização Portaria</button></td>
            </tr>
          </table>
          ', $card_at); 
        }else if($agendamento['vistoriador']==$user_id or $agendamento['vistoriador']==null or $agendamento['vistoriador']==0){
          $card_at=str_replace('<!-- botao_iniciar -->','<a href="vistoriador_iniciar_vistoria.php?id='.$agendamento['id'].'" class="btn btn-primary">Iniciar Vistoria</a>', $card_at);
        }else{
          $card_at=str_replace('<!-- botao_iniciar -->','<b>Reservado outro vistoriador</b>', $card_at);
          $ag_block=1;
        }

        //substituições
        if(isset($VISTORIADORES[$agendamento['vistoriador']])){$vistoriador_at=$VISTORIADORES[$agendamento['vistoriador']];}else{$vistoriador_at='';}
        $card_at=str_replace('<!--tipo_vistoria-->',$TIPO_VISTORIA[$agendamento['tipo_vistoria_id']],$card_at);
        $card_at=str_replace('<!--vtipo-->',$agendamento['tipo_vistoria_id'],$card_at);
        $card_at=str_replace('<!--cod_contrato-->',$agendamento['contrato_cod'],$card_at);
        $card_at=str_replace('<!--ch_local-->',$agendamento['ch_local'],$card_at);
        $card_at=str_replace('<!--vistoriador-->', $vistoriador_at,$card_at);
        $card_at=str_replace('<!--horario_agendamento-->', $agendamento_dinamico,$card_at);
        $card_at=str_replace('<!--block-->', $ag_block,$card_at);        
        $card_at=str_replace('<!--endereco_completo-->',$endereco_completo, $card_at);
        $card_at=str_replace('<!--lista_documentos-->',$lista_documentos, $card_at);
        $card_at=str_replace('<!--tempo_estimado-->',$tempo_estimado_txt, $card_at);
        
        
        
        
        

        //retorna card
        if($vistoria_entrada_indicada==1){
          //coloca no topo da fila se for a proxima vistoria de entrada
          $html_card=$card_at.$html_card;
        }else{
          //coloca no final da fila
          $html_card.=$card_at;
        }
        
      }
    }else{
      
    }
    echo $html_card;


    
  
  ?>


    <!-- Modal de Confirmação Bloqueio -->
    <div class="modal fade" id="confirmModalBlock" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="confirmModalLabel">Confirmar Bloqueio</h5>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Deseja bloquear a solicitação de vistoria?
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <!-- Chame a função para redirecionar com o ID do item como parâmetro GET -->
            <a id="allowLink" href="#" class="btn btn-danger">Sim</a>
            </div>
        </div>
        </div>
    </div>
  </div>

  <!-- Modal de Confirmação Assinatura -->
    <div class="modal fade" id="confirmModalAssinar" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="confirmModalLabel">Confirmar Assinatura</h5>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Você finalizou totalmente a vistoria?
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <!-- Chame a função para redirecionar com o ID do item como parâmetro GET -->
            <a id="allowLinkAssinar" href="#" class="btn btn-danger">Sim</a>
            </div>
        </div>
        </div>
    </div>
  </div>
  
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  

</body>
</html>
