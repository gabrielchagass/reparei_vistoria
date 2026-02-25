<?php
require_once('conexao.php');
$LABELSN=array('','X');
$PONTOS=array(0,8,10,13);
if(isset($_GET['m_ant'])){
  $m_ant=$_GET['m_ant'];
}else{
  $m_ant=0;
}


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
  }
//final da consulta
          
$data_fim_ini=date('Y-m-d H:i:s', mktime(0,0,0,date('m')-1-$m_ant,1,date('Y')));
$data_fim_fim=date('Y-m-d H:i:s', mktime(23,59,59,date('m')-$m_ant,(1-1),date('Y')));

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
  </style>
</head>
<body>
  <div class="container">
    <div>Realizadas entre <?php echo $data_fim_ini.' e '.$data_fim_fim; ?></div>
    <h1>
    <!-- Lista de agendamentos -->
    <table class="table" style="font-size:9pt;">
      <thead>
        <tr>
          <th>ID</th>
          <th>COD</th>
          <th>Tipo</th>
          <th>Vistoriador</th>
          <th>Data de Conclusao</th>
          <th>a/c</th>
          <th>Tamanho</th>
          <th>Termo</th>
          <th>modelo</th>
          <th>Testes</th>
          <th>Pontos</th>
          <th>soma</th>
        </tr>
      </thead>
      <tbody>
        <!-- Loop pelos agendamentos -->
        <?php
          // Consulta SQL para obter os agendamentos
          $sql = "SELECT * FROM cargos WHERE cargo = 'vistoriador'";
          $TIPO_VISTORIA=array('','Entrada','Saida','capta');
          $TAMANHO_IMOVEL=array('','pequeno','médio','grande');
          $VISTORIADORES=ARRAY();
          $VISTORIADORES['']='';
          $result = $conn->query($sql);
            while ($agendamento = $result->fetch_assoc()) {
                $VISTORIADORES[$agendamento['id']]=array_shift(explode('@',$agendamento['email']));
            }         

            $contagem=array();
            $contagem_pontos=array();
            
            foreach($VISTORIADORES as $key_a=>$vistoriador_at){
              $contagem[$key_a]=array();
              foreach($TIPO_VISTORIA as $key_b=>$tipo_at){
                $contagem[$key_a][$key_b]=array();
                foreach($TAMANHO_IMOVEL as $key_c=>$tamanho_at){
                  $contagem[$key_a][$key_b][$key_c]=0;     
                  $contagem_pontos[$key_a][$key_b][$key_c]=0;               
                }
              }
            }

          $sql = "SELECT * FROM agendamentos WHERE status_id = 4 and (data_fim >= '$data_fim_ini' AND data_fim <= '$data_fim_fim') and deleted_at is NULL ".$filtro_pes." ORDER BY vistoriador ASC, tipo_vistoria_id ASC, imovel_tamanho_id ASC, data_fim ASC";
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

               
                $ponto_at=0;
                if(
                  $agendamento['termoassinado']==1 
                  and $agendamento['feitopadrao']==1 
                  and $agendamento['testesrealizados']==1
                ){
                  $ponto_at=$PONTOS[$agendamento['imovel_tamanho_id']]/4;
                }

                if(!isset($contagem[$agendamento['vistoriador']][$agendamento['tipo_vistoria_id']][$agendamento['imovel_tamanho_id']])){
                  $contagem[$agendamento['vistoriador']][$agendamento['tipo_vistoria_id']][$agendamento['imovel_tamanho_id']]=0;
                  $contagem_pontos[$agendamento['vistoriador']][$agendamento['tipo_vistoria_id']][$agendamento['imovel_tamanho_id']]=0;
                }
                $contagem[$agendamento['vistoriador']][$agendamento['tipo_vistoria_id']][$agendamento['imovel_tamanho_id']]++;
                $contagem_pontos[$agendamento['vistoriador']][$agendamento['tipo_vistoria_id']][$agendamento['imovel_tamanho_id']]+=$ponto_at;
                
              echo '<tr class="lin_st">
                <td>'.$agendamento['id'].'</td>
                <td>'.$agendamento['contrato_cod'].'</td>
                <td>'.$TIPO_VISTORIA[$agendamento['tipo_vistoria_id']].'</td>
                <td>'.$VISTORIADORES[$agendamento['vistoriador']].'</td>';
                /**
                <!--
                <td>'.$agendamento['imovel_endereco'].' - nº '.$agendamento['imovel_numero'].' '.$agendamento['imovel_complemento'].'</td>
                -->
                **/
                echo '
                <td>'.$agendamento['data_fim'].'</td>
                <td>'.$agendamento['imovel_area_construida'].'</td>
                <td>'.$agendamento['imovel_tamanho_id'].'</td>
                
                <td>'.$LABELSN[$agendamento['termoassinado']].'</td>
                <td>'.$LABELSN[$agendamento['feitopadrao']].'</td>
                <td>'.$LABELSN[$agendamento['testesrealizados']].'</td>
                <td>'.$ponto_at.'</td>
                <td>'.$contagem_pontos[$agendamento['vistoriador']][$agendamento['tipo_vistoria_id']][$agendamento['imovel_tamanho_id']].'</td>
              </tr>';
            }
          } else {
            echo "<tr><td colspan='5'>Nenhum agendamento encontrado.</td></tr>";
          }



          echo '      </tbody>
          </table>
          <p>&nbsp;</p>
          <p>&nbsp;</p>
          <p>&nbsp;</p>
          <table class="table" style="font-size:9pt;">';

          foreach($VISTORIADORES as $key_a=>$vistoriador_at){
            foreach($TIPO_VISTORIA as $key_b=>$tipo_at){
              if($key_a>0 and $key_b>0){
                echo '
                <tr>';
                $soma_at=0;
                $soma_pontos=0;
                echo '<td>'.$vistoriador_at.'</td><td>'.$tipo_at.'</td><td></td><td></td>';   
                foreach($TAMANHO_IMOVEL as $key_c=>$tamanho_at){
                    if($key_c>0){
                      $soma_at+=$contagem[$key_a][$key_b][$key_c];
                      $soma_pontos+=$contagem_pontos[$key_a][$key_b][$key_c];
                      echo '<td></td><td></td><td>'.$tamanho_at.'</td><td>'.$contagem[$key_a][$key_b][$key_c].'</td>';            
                    }
                }
                echo '<td></td><td></td><td>SOMA:</td><td>'.$soma_at.'</td><td>PONTOS:</td><td>'.$soma_pontos.'</td>';   
              }
            }
          }

        ?>
      </tbody>
    </table>

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
          $('#data_agendamento').attr('min', moment(agendamento.prazo_inicio).format("YYYY-MM-DDTHH:mm"));
          $('#prazo_fim').text(moment(agendamento.prazo_fim).format("DD/MM/YYYY HH:MM"));
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
    function atualizarAgendamento(agendamentoId, vistoriador, dataAgendamento) {
      // Objeto com os dados do agendamento
      var dadosAgendamento = {
        id: agendamentoId,
        vistoriador: vistoriador,
        data_agendamento: dataAgendamento
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

      // Chamar a função para atualizar o agendamento
      atualizarAgendamento(agendamentoId, vistoriador, dataAgendamento);
    });
  });
</script>

</body>
</html>
<?php
    // Fechar conexão com o banco de dados
    $conn->close();
?>
