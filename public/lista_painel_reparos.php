<?php
require_once('conexao.php');
require_once('func_calc_agendamento.php');
$tipo_v=Array('',"Entrada","saida","Fotos");
$ultdia_comp=date('Ymd');
$html_linhas='';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agendamentos</title>
  <!-- CSS do Bootstrap -->
  <style>
    .st_4{
      display:none;
    }
    .block_1{
      color:#c0c0c0;
    }
    .futuro_1{
      color:#c0c0c0;
    }

    .separador_superior{
      border-top:solid 2px #000;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>
    <label for="campo_select">Lista de Agendamentos</label>
      <select class="form-control" id="list_status" name="list_status" onchange="aplicarFiltro(this.value)">
        <option value="1">Pendentes</option>
        <option value="4">Concluidos</option>
      </select>
    </h1>

    <!-- Lista de agendamentos -->
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>COD</th>
          <th>Tipo</th>
          <th>Vistoriador</th>
          <th>Endereço</th>
          <th>T.E.</th>
          <th>Data de Agendamento</th>
          <th>Ações</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <!-- Loop pelos agendamentos -->
        <?php
        //busca fila de vistorias
        include('buscar_proximo_da_fila.php');


          // Consulta SQL para obter os agendamentos
          $sql = "SELECT * FROM cargos WHERE cargo LIKE '%vistoriador%'";
          $VISTORIADORES=ARRAY();
          $VISTORIADORES['']='';
          $result = $conn->query($sql);
            while ($agendamento = $result->fetch_assoc()) {
                $parts = explode('@', $agendamento['email']);
                $VISTORIADORES[$agendamento['id']] = array_shift($parts);
            }         

            $mes_atras=date('Y-m-d H:i:s', mktime(0,0,0,date('m'),(date('d')-30),date('Y')));
          $sql = "SELECT * FROM agendamentos WHERE solicitacao_bloqueada = 0 AND  deleted_at IS NULL AND (data_fim >= '$mes_atras' OR data_fim IS NULL) ORDER BY prazo_dinamico ASC";
          $result = $conn->query($sql);

          // Verificar se há registros retornados
          if ($result->num_rows > 0) {
            // Loop pelos agendamentos
            while ($agendamento = $result->fetch_assoc()) {
                if((date('Ymd',strtotime($agendamento['prazo_inicio']))+0)>(date('Ymd')+0)){
                    $p_inicio='Entre '.date('d/m/Y',strtotime($agendamento['prazo_inicio'])).' e ';
                }else{
                    $p_inicio='';
                }

                //verificações
                $is_proxima=false;
                $is_em_andamento=false;
                if($proxima==$agendamento['id']){$is_proxima=true;}

                //separador de data
                $separador='';
                $dtcomp_agendamento=date("Ymd",strtotime($agendamento['data_agendamento']))+0;
                $dtcomp_prazo=date("Ymd",strtotime($agendamento['prazo_dinamico']))+0;
                //$logcomp='A('.$agendamento['data_agendamento'].' != null and '.$dtcomp_agendamento.' != '.$ultdia_comp.')<p>B('.$agendamento['data_agendamento'].' == null and '.$dtcomp_prazo.' != '.$ultdia_comp.')';
                if($agendamento['status_id'] == 1 and !$is_proxima){
                   //somente vistorias pendentes
                    if(($agendamento['data_agendamento'] != null and $agendamento['data_agendamento'] != '0000-00-00 00:00:00' and $dtcomp_agendamento!=$ultdia_comp)){
                      //$logcomp='DEFINIDO ('.$dtcomp_agendamento.') A('.$agendamento['data_agendamento'].' != null and '.$dtcomp_agendamento.' != '.$ultdia_comp.')';
                      $ultdia_comp=$dtcomp_agendamento;
                      $separador='superior';
                    }else if((($agendamento['data_agendamento'] == null or $agendamento['data_agendamento'] == '0000-00-00 00:00:00') and $dtcomp_prazo!=$ultdia_comp)){
                      //$logcomp='DEFINIDO ('.$dtcomp_prazo.') B('.$agendamento['data_agendamento'].' == null and '.$dtcomp_prazo.' != '.$ultdia_comp.')';
                      $ultdia_comp=$dtcomp_prazo;
                      $separador='superior';
                    } 
                }
                

                //calcula horario agendamento
                $txt_agendamento=calc_agendamento($agendamento['data_agendamento'],$agendamento['prazo_inicio'],$agendamento['prazo_fim'],$agendamento['prazo_dinamico']);
                $ag_block=$txt_agendamento['ag_block'];
                $agendamento_at=$txt_agendamento['agendamento_at'];
                $agendamento_dinamico=$txt_agendamento['agendamento_dinamico'];
                $agendamento_futuro=$txt_agendamento['agendamento_futuro'];
              
                
              $linha_atual='';
              $linha_atual.='
              <tr class="lin_st st_'.$agendamento['status_id'].' block_'.$ag_block.' futuro_'.$agendamento_futuro.' separador_'.$separador.'">
                <td>'.$agendamento['id'].'</td>
                <td>'.$agendamento['contrato_cod'].'</td>
                <td>'.$tipo_v[$agendamento['tipo_vistoria_id']].'</td>
                <td>';
                  if(isset($VISTORIADORES[$agendamento['vistoriador']])){
                    $linha_atual.=$VISTORIADORES[$agendamento['vistoriador']];
                  }
                $linha_atual.='
                </td>
                <td>'.$agendamento['imovel_endereco'].' - nº '.$agendamento['imovel_numero'].' '.$agendamento['imovel_complemento'].' - '.$agendamento['imovel_bairro'].'</td>
                <td title="Área = '.$agendamento['imovel_area_construida'].'m²">'.round($agendamento['imovel_area_construida']*0.6).'min.</td>
                <td title="'.$agendamento_at.'">'.$agendamento_dinamico.'</td>
                <td>
                  <!-- Botão "Editar" -->
                  <button type="button" class="btn btn-primary editar-agendamento" data-id="'.$agendamento['id'].'">
                    Editar
                  </button>
                </td><td>';

                  if($agendamento['devolus_agendamento_id']!= null and $agendamento['devolus_agendamento_id']!=0){
                      $linha_atual.='
                        <button type="button" class="btn btn-danger cancelar-agendamento"><a href="vistoriador_cancelar_inicio.php?id='.$agendamento['id'].'" data-id="'.$agendamento['id'].'">
                        Cancelar</a>
                      </button>';
                    }
                  
              $linha_atual.='
                </td>
              </tr>';

              if($is_proxima){
                //se for o proximo da fila coloca no topo
                //$html_linhas=$linha_atual.$html_linhas;
                $html_linhas.=$linha_atual;
                
              }else{
                //senão coloca no final
                $html_linhas.=$linha_atual;
              }
            }

            
          } else {
            $html_linhas.="<tr><td colspan='5'>Nenhum agendamento encontrado.</td></tr>";
          }

          echo $html_linhas;
        ?>
      </tbody>
    </table>

<!-- Modal de edição -->
<div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="editarModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editarModalLabel">Editar Agendamento</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Formulário de edição -->
        <form id="editarForm" action="lista_painel_reparos_atualizar.php" method="post">
          <div class="form-group">
            <label for="contrato_cod"><b>Código do Contrato:</b></label>
            <span id="contrato_cod" name="contrato_cod"> </span>
            <input type="hidden" value="" name="agendamentoId" id="agendamentoId">
          </div>
          <div class="form-group">
            <label for="imovel_endereco"><b>Endereço do Imóvel:</b></label>
            <span type="text" id="imovel_endereco" name="imovel_endereco"></span> - 
            nº <span type="text" id="imovel_numero" name="imovel_numero"></span> - 
            <span type="text" id="imovel_complemento" name="imovel_complemento"></span> - 
            <span type="text" id="imovel_bairro" name="imovel_bairro"></span> - 
            <span type="text" id="imovel_cidade" name="imovel_cidade"></span> - 
            <span type="text" id="imovel_uf" name="imovel_uf"></span>
          </div>
          <div class="form-group">
            <label for="prazo_fim"><b>Prazo Final</b></label>
            <span id="prazo_fim" name="prazo_fim"></span>
          </div>
          <div class="form-group">
            <label for="vistoriador"><b>Vistoriador</b></label>
            <select class="form-control" id="vistoriador" name="vistoriador" value="">
                <?php
                    $fil='%vistoriador%';
                    $sqlC = "SELECT * FROM cargos WHERE cargo LIKE '$fil'";
                    $result = $conn->query($sqlC);
                    while ($cargos_sql = $result->fetch_assoc()) {
                        $nome_v=explode('@',$cargos_sql['email']);
                        $nome_v=$nome_v[0];
                        echo '
                        <option value="'.$cargos_sql['id'].'">'.$nome_v.'</option>';
                    }
                ?>
            </select>
        </div>
          <div class="form-group">
            <label for="data_agendamento"><b>Data de Agendamento</b></label>
            <input class="form-control" type="datetime-local" id="data_agendamento" name="data_agendamento">
          </div>

          <!-- Campos relacionados -->
          <div class="form-group">
            <label><b>Proprietários</b></label>
            <ul id="proprietarios"></ul>
          </div>
          <div class="form-group">
            <label><b>Inquilinos</b></label>
            <ul id="inquilinos"></ul>
          </div>
          <div class="form-group">
            <label><b>Fiadores</b></label>
            <ul id="fiadores"></ul>
          </div>

          <div class="form-group">
            <label for="solicitante"><b>Solicitante:</b></label>
            <span type="text" id="solicitante" name="solicitante"></span>
          </div>
          
          <div class="form-group">
            <div><b>Chave Local: </b><span id="ch_local"></span></div>
            <div><b>Padrão Vistoria: </b><span id="padrao_vistoria"></span></div>
            <div><b>Tamanho imóvel: </b><span id="imovel_tamanho_id"></span></div>
            <div><b>Mobiliado?: </b><span id="imovel_mobiliado"></span></div>
            <div><b>Controle: </b><span id="ch_qtd_controle"></span></div>
            <div><b>Cartão: </b><span id="ch_qtd_cartao"></span></div>
            <div><b>tag: </b><span id="ch_qtd_tag"></span></div>
            <div><b>correio: </b><span id="ch_qtd_correio"></span></div>
            <div><b>Carrinho: </b><span id="ch_qtd_carrinho"></span></div>
            <div><b>Data Solicitação: </b><span id="created_at"></span></div>

            <div><b>Nome do contato: </b><span id="nome_cliente"></span></div>
            <div><b>WhatsApp: </b><span id="whatsapp"></span></div>
            <div><b>Observacoes: </b><span id="obs_contato"></span></div>
            
            <div></div>
            <div>CONTROLE DE QUALIDADE</div>
              
            <div class="form-group">
              <label for="termoassinado"><b>Termo Assinado</b></label>
              <select class="form-control" id="termoassinado" name="termoassinado" value="">
                  <option value="1">SIM</option>
                  <option value="0">NÃO</option>
              </select>
            </div>  
            <div class="form-group">
              <label for="feitopadrao"><b>Modelo padrão de vistoria</b></label>
              <select class="form-control" id="feitopadrao" name="feitopadrao" value="">
                  <option value="1">SIM</option>
                  <option value="0">NÃO</option>
              </select>
            </div>  
            <div class="form-group">
              <label for="testesrealizados"><b>Testes Realizados</b></label>
              <select class="form-control" id="testesrealizados" name="testesrealizados" value="">
                  <option value="1">SIM</option>
                  <option value="0">NÃO</option>
              </select>
            </div>
            <div class="form-group">
              <label for="descricaopendencias"><b>Descrição das pendencias</b></label>
              <input class="form-control" id="descricaopendencias" name="descricaopendencias" value="" />
            </div>
            


            <button type="submit" class="btn btn-primary">Salvar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
  </div>

  
  <!-- biblioteca moment-->
	<script src="moment.js"></script>

  <!-- Código jQuery para preencher os dados do modal -->
<script>
  function aplicarFiltro(valor) {
    if(valor==1){
      //pendentes
      $('.lin_st').show();
      $('.st_4').hide();
    }else if(valor==4){
      //concluidos
      $('.lin_st').hide();
      $('.st_4').show();
    }
  }  

  $(document).ready(function() {
    $('.editar-agendamento').on('click', function() {
      var agendamentoId = $(this).data('id');

      // Fazer requisição Ajax para obter os dados do agendamento
      $.ajax({
        url: 'obter_agendamento.php',
        type: 'GET',
        data: { id: agendamentoId },
        success: function(response) {
          var agendamento = JSON.parse(response);

          // Preencher os campos do formulário
          $('#agendamentoId').val(agendamento.id);
          $('#vistoriador').val(agendamento.vistoriador);
          $('#data_agendamento').val(agendamento.data_agendamento);
          $('#termoassinado').val(agendamento.termoassinado);
          $('#feitopadrao').val(agendamento.feitopadrao);
          $('#testesrealizados').val(agendamento.testesrealizados);
          $('#descricaopendencias').val(agendamento.descricaopendencias);
          $('#data_agendamento').attr('min', moment(agendamento.prazo_inicio).format("YYYY-MM-DDTHH:mm"));
          $('#prazo_fim').text(moment(agendamento.prazo_fim).format("DD/MM/YYYY HH:MM"));
          $('#prazo_dinamico').text(moment(agendamento.prazo_dinamico).format("DD/MM/YYYY HH:MM"));
          $('#contrato_cod').text(agendamento.contrato_cod);
          $('#solicitante').text(agendamento.solicitante);
          $('#imovel_endereco').text(agendamento.imovel_endereco);
          $('#imovel_numero').text(agendamento.imovel_numero);
          $('#imovel_complemento').text(agendamento.imovel_complemento);
          $('#imovel_bairro').text(agendamento.imovel_bairro);
          $('#imovel_cidade').text(agendamento.imovel_cidade);
          $('#imovel_uf').text(agendamento.imovel_uf);
          $('#padrao_vistoria').text(agendamento.padrao_vistoria);

          
          $('#ch_local').text(agendamento.ch_local);
          $('#padrao_vistoria').text(agendamento.padrao_vistoria);
          $('#imovel_tamanho_id').text(agendamento.imovel_tamanho_id);
          $('#imovel_mobiliado').text(agendamento.imovel_mobiliado);
          $('#ch_qtd_controle').text(agendamento.ch_qtd_controle);
          $('#ch_qtd_cartao').text(agendamento.ch_qtd_cartao);
          $('#ch_qtd_tag').text(agendamento.ch_qtd_tag);
          $('#ch_qtd_correio').text(agendamento.ch_qtd_correio);
          $('#ch_qtd_carrinho').text(agendamento.ch_qtd_carrinho);
          $('#created_at').text(agendamento.created_at);

          $('#nome_cliente').text(agendamento.nome_cliente);
          $('#whatsapp').text(agendamento.whatsapp);
          $('#obs_contato').text(agendamento.obs_contato);

          // Preencher os campos relacionados
          var proprietariosHtml = '';
          agendamento.proprietarios.forEach(function(proprietario) {
            proprietariosHtml += '<li>' + proprietario.nome + '</li>';
          });
          $('#proprietarios').html(proprietariosHtml);

          var inquilinosHtml = '';
          agendamento.inquilinos.forEach(function(inquilino) {
            inquilinosHtml += '<li>' + inquilino.nome + '</li>';
          });
          $('#inquilinos').html(inquilinosHtml);

          var fiadoresHtml = '';
          agendamento.fiadores.forEach(function(fiador) {
            fiadoresHtml += '<li>' + fiador.nome + '</li>';
          });
          $('#fiadores').html(fiadoresHtml);

          // Abrir o modal de edição
          $('#editarModal').modal('show');
        }
      });
    });

    // Função para atualizar o agendamento
    function atualizarAgendamento(agendamentoId, vistoriador, dataAgendamento, termoassinado, feitopadrao, testesrealizados, descricaopendencias) {
      // Objeto com os dados do agendamento
      //, termoassinado, feitopadrao, testesrealizados, descricaopendencias
      var dadosAgendamento = {
        id: agendamentoId,
        vistoriador: vistoriador,
        data_agendamento: dataAgendamento,
        termoassinado: termoassinado,
        feitopadrao: feitopadrao,
        testesrealizados: testesrealizados,
        descricaopendencias: descricaopendencias
      };

      // Fazer requisição Ajax para atualizar o agendamento
      $.ajax({
        url: 'lista_painel_reparos_atualizar.php',
        type: 'POST',
        data: JSON.stringify(dadosAgendamento),
        contentType: 'application/json',
        success: function(response) {
          var resultado = JSON.parse(response);

          if (resultado.success) {
            alert(resultado.message);
            // Fechar o modal de edição após a atualização
            $('#editarModal').modal('hide');
            // Atualizar a página ou executar outras ações necessárias
          } else {
            alert('Erro ao atualizar agendamento: ' + resultado.message);
          }
        }
      });
    }

    // Evento de submit do formulário de edição
    $('#editarForm').on('submit', function(event) {
      event.preventDefault();

      // Obter os valores dos campos do formulário
      var agendamentoId = $('#agendamentoId').val();
      var vistoriador = $('#vistoriador').val();
      var dataAgendamento = $('#data_agendamento').val();
      var termoassinado = $('#termoassinado').val();
      var feitopadrao = $('#feitopadrao').val();
      var testesrealizados = $('#testesrealizados').val();
      var descricaopendencias = $('#descricaopendencias').val();

      // Chamar a função para atualizar o agendamento
      atualizarAgendamento(agendamentoId, vistoriador, dataAgendamento, termoassinado, feitopadrao, testesrealizados, descricaopendencias);
      
    });
  });
</script>

</body>
</html>
<?php
    // Fechar conexão com o banco de dados
    $conn->close();
?>
