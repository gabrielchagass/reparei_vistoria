<?php
require_once 'conexao.php';
require_once('calc_horas.php');
if(!isset($filtro)){die('erro, filtro nao encontrado');}

//VARIAVEIS GLOBAIS
$TIPO_VISTORIA=array('','Entrada','Saida','Fotos');
?>
<style>
  .st_4{
    display:none;
  }

td[draggable="true"]{
    cursor: move;
    transition: transform 0.3s ease;
}
td.dragging {
    opacity: 0.5;
    transform: scale(1.05);
    border:dashed 1px #777;
}

td.moving {
    transition: background-color 0.3s ease;
    background-color: #d1e7dd; /* Cor de fundo ao mover */
}

td.over{
    border:solid 1px #777;
}

.not_editable{
    color:#000 !important;
    cursor: default !important;
}
</style>

<script>
    $(document).ready(function() {
    //modal de confirmação
    var confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));
    let draggingRow = null;
    let targetRow = null;  // Definido fora do escopo para ser acessível em qualquer parte

    // Ação ao clicar no botão "Confirmar"
    document.getElementById('confirmBtn').addEventListener('click', function() {
        if (draggingRow && targetRow) {
            //desabilita botão
            $('#confirmBtn').prop('disabled', true);
            $('#confirmBtn').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Carregando...');
            
            // Clona ambas as linhas
            const draggingClone = draggingRow.clone(true);
            const targetClone = targetRow.clone(true);
            const draggingClone_tr = draggingRow.closest('tr')
                .clone(true)
                .find('td').removeClass('over dragging').end();
            const targetClone_tr = targetRow.closest('tr')
                .clone(true)
                .find('td').removeClass('over dragging').end();

            //coleta datas
            var data_origem=draggingClone.find('a').html();
            var data_destino=targetClone.find('a').html();



            //define id
            var id_ocod=draggingRow.attr('id').replace('dr_','');
            var id_dcod=targetRow.attr('id').replace('dr_','');
            var txt_motivo=$('#modal_motivo').val();

            $.ajax({
                    url: 'cronograma_inverter.php', // Arquivo PHP para buscar os dados do agendamento
                    type: 'POST',
                    data: { 
                        ocod: id_ocod,
                        dcod: id_dcod,
                        motivo: txt_motivo,
                     },
                    dataType: 'html',
                    success: function(response) {
                        //inverte linhas

                        // Troca as posições
                        draggingRow.closest('tr').replaceWith(targetClone_tr);
                        targetRow.closest('tr').replaceWith(draggingClone_tr);
                        targetClone_tr.find('td.data').find('a.content_data').html(data_origem);
                        draggingClone_tr.find('td.data').find('a.content_data').html(data_destino);

                        //apaga motivo 
                        $('#modal_motivo').val('');

                        // Reatribui os eventos aos novos elementos após a troca
                        assignDragEvents();

                        // Reseta as variáveis de linha arrastada e alvo
                        draggingRow = null;
                        targetRow = null;

                        // Fecha o modal
                        confirmModal.hide();

                        //habilita botão
                        $('#confirmBtn').prop('disabled', false);
                        $('#confirmBtn').html('Confirmar');
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                        
                        //apaga motivo 
                        $('#modal_motivo').val('');

                         // Remove as classes "over" e "dragging"
                        draggingClone.removeClass('over dragging');
                        targetClone.removeClass('over dragging');

                        // Reatribui os eventos aos novos elementos após a troca
                        assignDragEvents();

                        // Reseta as variáveis de linha arrastada e alvo
                        draggingRow = null;
                        targetRow = null;

                        // Fecha o modal
                        confirmModal.hide();

                        //habilita botão
                        $('#confirmBtn').prop('disabled', false);
                        $('#confirmBtn').html('Confirmar');
                    }
            });

           
        }
    });

    // Ação ao clicar no botão "Cancelar"
    document.getElementById('cancelBtn').addEventListener('click', function() {
        // Fecha o modal sem aplicar as alterações
        confirmModal.hide();
        
       $('#confirmBtn').prop('disabled', false);

    });
    // Ação quando o modal for fechado (inclui clique fora)
    $('#confirmModal').on('hidden.bs.modal', function () {
        /*
        // Remove as classes "over" e "dragging"
        draggingRow.removeClass('over dragging');
        targetRow.removeClass('over dragging');

        // Aqui você pode adicionar qualquer ação, como resetar variáveis
        draggingRow = null;
        targetRow = null;
        */
    });

    // Função para atribuir eventos de arrastar e soltar
    function assignDragEvents() {
        $('td[draggable="true"]').off(); // Remove qualquer evento antigo antes de reatribuir.

        // Inicia o arrasto da linha
        $('td[draggable="true"]').on('dragstart', function() {
            draggingRow = $(this);  // Definir a linha arrastada
            draggingRow.addClass('dragging');
        });

        // Finaliza o arrasto (não resetar draggingRow aqui)
        $('td[draggable="true"]').on('dragend', function() {
            draggingRow.removeClass('dragging');
            // Remover reset de draggingRow aqui para manter o valor até o modal
        });

        // Permite que o item seja solto na linha alvo
        $('td[draggable="true"]').on('dragover', function(e) {
            e.preventDefault();
            $(this).addClass('over');
        });

        // Remove a classe de destaque ao sair de uma linha durante o arrasto
        $('td[draggable="true"]').on('dragleave', function() {
            $(this).removeClass('over');
        });

        // Troca a posição ao soltar
        $('td[draggable="true"]').on('drop', function(e) {
            e.preventDefault();
            targetRow = $(this); // Definindo a linha alvo aqui

            // Verifica se está soltando em uma linha diferente
            if (draggingRow[0] !== targetRow[0]) {
                //preencher dados
                var id_dr=draggingRow.attr('id').replace('dr_','');
                var id_tr=targetRow.attr('id').replace('dr_','');
                $('#Ocode').html($('#cod_'+id_dr).html());
                $('#Dcode').html($('#cod_'+id_tr).html());
                $('#Ologra').html($('#logra_'+id_dr).html());
                $('#Dlogra').html($('#logra_'+id_tr).html());
                $('.Odata').html($('#dtval_'+id_dr).html());
                $('.Ddata').html($('#dtval_'+id_tr).html());

                // Exibir o modal de confirmação
                confirmModal.show();
            }else{
                //arrastou em cima dele mesmo
                // Remove as classes "over" e "dragging"
                draggingRow.removeClass('over dragging');
                targetRow.removeClass('over dragging');

                // Aqui você pode adicionar qualquer ação, como resetar variáveis
                draggingRow = null;
                targetRow = null;
            }
        });
    }

    // Inicializa os eventos de arrasto ao carregar a página
    assignDragEvents();
});

</script>

<!-- Modal de confirmação Bootstrap -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalLabel">Alterar prazo de vistoria</h5>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <div style="text-align:center;"><b>Alterações:</b></div>
        <p>&nbsp;</p>
        <table style="width:100%;">
            <tr>
                <td style="width: 50%;text-align:center;border-right:dashed 1px #c0c0c0;"><b id="Ocode">0000-000/0</b></td>
                <td style="width: 50%;text-align:center;"><b id="Dcode">0000-000/0</b></td>
            </tr>
            <tr>
                <td style="width: 50%;text-align:center;border-right:dashed 1px #c0c0c0;"><small id="Ologra">Rua teste do tesne nº 0 - a</small></td>
                <td style="width: 50%;text-align:center;"><small id="Dlogra"> teste do tesne nº 0 - b</small></td>
            </tr>
            <tr>
                <td style="width: 50%;text-align:center;border-right:dashed 1px #c0c0c0;font-size:8pt;">&nbsp;</td>
                <td style="width: 50%;text-align:center;font-size:8pt;">&nbsp;</td>
            </tr>
            <tr>
                <td style="width: 50%;text-align:center;border-right:dashed 1px #c0c0c0;"><div><small>de: </small></div><div><b class="Odata">00/00/0000 00:00</b></div></td>
                <td style="width: 50%;text-align:center;"><div><small>de: </small></div><div><b class="Ddata">00/00/0000 00:00</b></div></td>
            </tr>
            <tr>
                <td style="width: 50%;text-align:center;border-right:dashed 1px #c0c0c0;font-size:8pt;">&nbsp;</td>
                <td style="width: 50%;text-align:center;font-size:8pt;">&nbsp;</td>
            </tr>
            <tr>
                <td style="width: 50%;text-align:center;border-right:dashed 1px #c0c0c0;"><div><small>para: </small></div><div><b class="Ddata">00/00/0000 00:00</b></div></td>
                <td style="width: 50%;text-align:center;"><div><small>para: </small></div><div><b class="Odata">00/00/0000 00:00</b></div></td>
            </tr>
        </table>
        <div>&nbsp;</div>
        <div style="text-align:center;"><b>Digite o Motivo</b></div>
        <div><textarea id="modal_motivo" style="width:100%;height:90px;"></textarea></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal" id="cancelBtn">Cancelar</button>
        <button type="button" class="btn btn-primary" id="confirmBtn">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<?php
include('buscar_proximo_da_fila.php');
?>

    <div class="container">
        <h2>
            <label for="campo_select">Lista de Vistorias Concluidas</label>
        </h2>

        <?php
        // Consulta SQL para selecionar os registros da tabela "agendamentos" com data_fim nulo
        $sql = "SELECT * FROM agendamentos WHERE deleted_at IS NULL AND data_fim IS NOT NULL AND data_assinatura IS NOT NULL AND (data_assinatura >= CURDATE() - INTERVAL 15 DAY) ".$filtro." ORDER BY data_assinatura desc";
        
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo '<table class="table table-striped">';
            echo '<thead class="thead-dark">';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>Contrato Cod</th>';
            echo '<th>Imóvel</th>';
            echo '<th>Tipo</th>';
            echo '<th>Data Conclusão</th>';
            echo '<th>Ações</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

                
            $dt_hoje=date("Ymd")+0;
            $dt_ontem=$dt_hoje-1;
            $dt_seisdias=$dt_hoje-6;
            $diasem=array('Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sabado');
            
            // Exibe os dados de cada registro
            while ($row = $result->fetch_assoc()) {

                $ag_calc=date("Ymd",strtotime($row['data_assinatura']))+0;
                if($ag_calc == $dt_hoje){
                    $data_assinatura='às '.date("H:i",strtotime($row['data_assinatura']));
                }else if($ag_calc == $dt_ontem){
                    $data_assinatura='Ontem, às '.date("H:i",strtotime($row['data_assinatura']));
                }else if($ag_calc >= $dt_seisdias){
                    $data_assinatura=''.$diasem[date("w",strtotime($row['data_assinatura']))].', às '.date("H:i",strtotime($row['data_assinatura']));
                }else{
                    $data_assinatura=''.date("d/m/Y H:i",strtotime($row['data_assinatura']));
                }


                echo '<tr class="table-row" id="TR'.$row['id'].'">';
                echo '<td id="id_'.$row['id'].'" class="id'.$row['id'].'">'.$row['id'].'</td>';
                echo '<td id="cod_'.$row['id'].'">' . $row['contrato_cod'] . '</td>';
                echo '<td id="logra_'.$row['id'].'">' . $row['imovel_endereco'] . ', '. $row['imovel_numero'].' '.$row['imovel_complemento'].' </td>';
                echo '<td>'.$TIPO_VISTORIA[$row['tipo_vistoria_id']]. '</td>';
                echo '<td>'. $data_assinatura .'</td>';
                    if($row['devolus_vistoria_id']){
                        echo '
                        <td>
                            <div class="d-flex gap-2">
                                <a href="vistoria_baixar.php?id_devolus='.$row['devolus_vistoria_id'].'&nome='.urlencode($row['contrato_cod']).'&foto=true" 
                                    class="btn btn-primary" 
                                    target="_blank">
                                    <i class="bi bi-camera"></i> Vistoria com Foto
                                </a>

                                <a href="vistoria_baixar.php?id_devolus='.$row['devolus_vistoria_id'].'&nome='.urlencode($row['contrato_cod']).'&foto=false" 
                                    class="btn btn-secondary" 
                                    target="_blank">
                                    <i class="bi bi-file-earmark-text"></i> Vistoria sem Foto
                                </a>
                            </div>
                        </td>';
                    }
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
        } else {
            echo '<div class="alert alert-info" role="alert">Nenhum registro encontrado</div>';
        }

        ?>

        <!-- Modal de Edição -->
        <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Editar Agendamento</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="editForm">
                            <!-- Campos do formulário de edição -->
                            <div class="form-group">
                                <label for="editId">ID:</label>
                                <input type="text" class="form-control" id="editId" name="id" readonly>
                            </div>
                            <div class="form-group">
                                <label for="editContratoCod">Contrato Cod:</label>
                                <input type="text" class="form-control" id="editContratoCod" name="contrato_cod" readonly>
                            </div>
                            <div class="form-group">
                                <label for="editImovelDisponivel">Imóvel Disponível:</label>
                                <select class="form-control" id="editImovelDisponivel" name="imovel_disponivel">
                                    <option value="1">Sim</option>
                                    <option value="0">Não</option>
                                </select>
                                <input type="hidden" id="ini_imovel_disponivel" name="ini_imovel_disponivel" />
                                <input type="hidden" id="ini_prazo_inicio" name="ini_prazo_inicio" />
                            </div>
                            <div class="form-group">
                                <label for="editDisponibilidadeMotivo">Motivo da indisponibilidade:</label>
                                <input type="text" class="form-control" id="editDisponibilidadeMotivo" name="disponibilidade_motivo" readonly>
                            </div>
                            <div class="form-group">
                                <label for="editPrazoInicio">Disponível a partir de:</label>
                                <input type="date" class="form-control" id="editPrazoInicio" name="prazo_inicio" readonly>
                            </div>
                            <div class="form-group">
                                <label for="editPrazo">Prazo:</label>
                                <input type="text" class="form-control" id="editPrazo" name="prazo_fim">
                            </div>
                            <button type="submit" class="btn btn-primary">Salvar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal de Confirmação -->
    <div class="modal fade" id="confirmModalCancel" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="confirmModalLabel">Confirmar Exclusão</h5>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
            Tem certeza de que deseja excluir este item?
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <!-- Chame a função para redirecionar com o ID do item como parâmetro GET -->
            <a id="deleteLink" href="#" class="btn btn-danger">Sim, Excluir</a>
            </div>
        </div>
        </div>
    </div>
    
    <!-- Modal de Confirmação Liberação -->
    <div class="modal fade" id="confirmModalAllow" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title" id="confirmModalLabel">Confirmar Liberação</h5>
            <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Este imóvel esta disponivel a partir de agora?
            </div>
            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            <!-- Chame a função para redirecionar com o ID do item como parâmetro GET -->
            <a id="allowLink" href="#" class="btn btn-success">Sim, Esta disponivel</a>
            </div>
        </div>
        </div>
    </div>


    <script>
        function exibirModal(itemId) {
            const deleteLink = document.getElementById('deleteLink');
            deleteLink.href = 'excluir_agendamento.php?id=' + itemId; // Adicione o ID ao link
            $('#confirmModalCancel').modal('show'); // Mostra o modal
        }
        function exibirModalliberacao(itemId) {
            const allowLink = document.getElementById('allowLink');
            allowLink.href = 'agendamento_liberar.php?id=' + itemId; // Adicione o ID ao link
            $('#confirmModalAllow').modal('show'); // Mostra o modal
        }
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
            //destaca uma linha
                // Obtém o valor do parâmetro "id" da URL
                const urlParams = new URLSearchParams(window.location.search);
                
                // Obtém uma referência para o elemento desejado
                if(typeof urlParams.get('id') != null){
                    const id = urlParams.get('id');
                    //alert(id);
                    if(typeof document.getElementById('TR'+id) != null && id != null){
                        var elemento = document.getElementById('TR'+id);
                        // Rola a página até que o elemento fique visível
                        elemento.scrollIntoView();
                            
                        // Localiza a linha da tabela com o ID correspondente
                        const linha = $('td.id' + id).closest('tr');

                        // Faz a linha piscar utilizando jQuery
                        // Aplica o efeito de piscar
                        linha.addClass('blink');

                        // Interrompe o efeito após 5 segundos
                        setTimeout(function() {
                        linha.removeClass('blink');
                        }, 5000);

                        // Aplica o efeito de piscar (fadeIn/fadeOut)
                        const interval = setInterval(function() {
                            linha.fadeOut(500).fadeIn(500);
                        }, 1000);

                        // Interrompe o efeito após 5 segundos
                        setTimeout(function() {
                            clearInterval(interval);
                            linha.stop().fadeIn();
                        }, 5000);

                    }
                }

            // Captura o evento de clique nos links de edição
            $('.edit-link').click(function(e) {
                e.preventDefault();
                // Obtém o ID do agendamento a ser editado
                var id = $(this).data('id');

                // Faz uma requisição AJAX para buscar os dados do agendamento
                $.ajax({
                    url: 'buscar_agendamento.php', // Arquivo PHP para buscar os dados do agendamento
                    type: 'GET',
                    data: { id: id },
                    dataType: 'json',
                    success: function(response) {
                        // Preenche os campos do formulário de edição com os dados retornados
                        $('#editId').val(response.id);
                        $('#editContratoCod').val(response.contrato_cod);
                        $('#editPrazo').val(moment(response.prazo_fim).format("DD/MM/YYYY HH:MM"));
                        $('#editImovelDisponivel').val(response.imovel_disponivel);
                        $('#ini_imovel_disponivel').val(response.imovel_disponivel);
                        $('#editDisponibilidadeMotivo').val(response.disponibilidade_motivo);
                        var prazoInicio = new Date(response.prazo_inicio);
                        var prazoInicioFormatted = prazoInicio.toISOString().split('T')[0];
                        $('#editPrazoInicio').val(prazoInicioFormatted);
                        $('#ini_prazo_inicio').val(prazoInicioFormatted);
                        
                        // Habilita ou desabilita os campos de motivo da disponibilidade e prazo de início conforme o valor de imovel_disponivel
                        if (response.imovel_disponivel === '1') {
                            $('#editDisponibilidadeMotivo').prop('readonly', false);
                            $('#editPrazoInicio').prop('readonly', false);
                        } else {
                            $('#editDisponibilidadeMotivo').prop('readonly', true);
                            $('#editPrazoInicio').prop('readonly', true);
                        }

                        if (response.imovel_disponivel === '0') {
                            $('#editDisponibilidadeMotivo').prop('readonly', false);
                            $('#editPrazoInicio').prop('readonly', false);
                        } else {
                            $('#editDisponibilidadeMotivo').prop('readonly', true);
                            $('#editPrazoInicio').prop('readonly', true);
                        }
                        $('#editPrazo').prop('readonly', true);
                        
                        // Abre o modal de edição
                        $('#editModal').modal('show');
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            });

            // Captura o evento de envio do formulário de edição
            $('#editForm').submit(function(e) {
                e.preventDefault();

                // Obtém os dados do formulário de edição
                var formData = $(this).serialize();

                // Faz uma requisição AJAX para atualizar os dados do agendamento
                $.ajax({
                    url: 'atualizar_agendamento.php', // Arquivo PHP para atualizar os dados do agendamento
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Atualiza a linha da tabela com os dados atualizados
                            var id = response.data.id;
                            var solicitante = response.data.solicitante;
                            var prazo = response.data.prazo;
                            var prazoFormatted = response.data.prazoFormatted;

                            var row = $('table tbody').find('tr[data-id="' + id + '"]');
                            row.find('td:eq(2)').text(solicitante);
                            row.find('td:eq(3)').html('<a href="#" class="edit-link" data-id="' + id + '">' + prazoFormatted + '</a>');

                            // Fecha o modal de edição
                            $('#editModal').modal('hide');
                        } else {
                            console.log(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                    }
                });
            });

            // Captura o evento de alteração do valor do campo imovel_disponivel
            $('#editImovelDisponivel').change(function() {
                var imovelDisponivel = $(this).val();

                // Habilita ou desabilita os campos de motivo da disponibilidade e prazo de início conforme o valor de imovel_disponivel
                if (imovelDisponivel === '0') {
                    $('#editDisponibilidadeMotivo').prop('readonly', false);
                    $('#editPrazoInicio').prop('readonly', false);
                } else {
                    $('#editDisponibilidadeMotivo').prop('readonly', true);
                    $('#editPrazoInicio').prop('readonly', true);
                    $('#editPrazoInicio').val(moment().format('YYYY-MM-DD'));
                }
                if($('#ini_imovel_disponivel').val()==$('#editImovelDisponivel').val()){
                    $('#editPrazoInicio').val(moment($('#ini_prazo_inicio').val(),'YYYY-MM-DD').format('YYYY-MM-DD'));
                }
            });

            ///adicionar prazo
			  // Função para calcular o horário de conclusão
				function calcularHorarioConclusao(prazo_inicio) {
					var hoje = new Date();
					var prazoInicio = prazo_inicio; // Obtém o valor do input prazo_inicio
					var prazoInicial;

					if (moment().isSame(moment(prazoInicio, 'YYYY-MM-DD'), 'day')) {
						prazoInicial = moment(prazoInicio + ' ' + moment().format('HH:mm'), 'YYYY-MM-DD HH:mm');
					} else {
						if (moment(prazoInicio, 'YYYY-MM-DD').format('YYYYMMDD') < moment().format('YYYYMMDD')) {
							prazoInicial = moment();
						}else{
							prazoInicial = moment(prazoInicio + ' 08:00', 'YYYY-MM-DD HH:mm');
						}
					}
					

					var horasTrabalhadas = 0;
					var dataAtual = prazoInicial.clone(); // Clona o prazo inicial para não alterar o objeto original

					// Verifica se é domingo
					if (dataAtual.isoWeekday() == 7) {
						dataAtual.add(1, 'day').hour(8).minute(0);  // Avança para o próximo dia
					}
					// Verifica se é sabado
					if (dataAtual.isoWeekday() == 6) {
						if(dataAtual.hour() >= 12){
							dataAtual.add(2, 'day').hour(8).minute(0); 
						}
					}

					// Verifica o horário inicial
					if (dataAtual.hour() < 8) {
					dataAtual.hour(8).minute(0); // Define o horário inicial como 08:00
					} else if (dataAtual.hour() >= 17) {
					dataAtual.add(1, 'day').hour(8).minute(0); // Avança para o próximo dia e define o horário inicial como 08:00
					}

					// Calcula as horas trabalhadas até atingir 20 horas
					while (horasTrabalhadas < 20) {
						if(dataAtual.isoWeekday()==6){
							if(dataAtual.hour() >= 12){
								//sabado apos meio dia
								dataAtual.add(2, 'day').hour(8).minute(0); // Avança para segunda
							}else{
								//sabado antes do meio dia
								dataAtual.add(1, 'hour'); // Avança uma hora
								horasTrabalhadas++; // Incrementa as horas trabalhadas
							}
						}else if (dataAtual.hour() >= 12 && dataAtual.hour() < 13) {
							dataAtual.hour(13).minute(0); // Define o horário de retorno do almoço como 13:00
						} else if (dataAtual.hour() >= 17) {
							dataAtual.add(1, 'day').hour(8).minute(0); // Avança para o próximo dia e define o horário inicial como 08:00
						} else {
							dataAtual.add(1, 'hour'); // Avança uma hora
							horasTrabalhadas++; // Incrementa as horas trabalhadas
						}
					}

					// Retorna o horário de conclusão formatado
					return dataAtual.format('DD/MM/YYYY HH:mm');
				}

				// Evento de alteração do input com ID "prazo_inicio"
				$('#editPrazoInicio').on('change', function() {
					var horarioConclusao = calcularHorarioConclusao($('#editPrazoInicio').val());
					$('#editPrazo').val(horarioConclusao); // Exibe o horário de conclusão do serviço no span com ID "lab_prazo"
				});

        });
    </script>
