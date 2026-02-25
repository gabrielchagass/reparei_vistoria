<?php
require_once 'g_ver_login.php';
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
	<title>Solicitação de vistoria</title>

	<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' name='viewport' />
    <meta name="viewport" content="width=device-width" />

	<link rel="apple-touch-icon" sizes="76x76" href="assets/img/apple-icon.png" />
	<link rel="icon" type="image/png" href="assets/img/favicon.png" />

	<!--     Fonts and icons     -->
	<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Roboto+Slab:400,700|Material+Icons" />
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" />

	<!-- CSS Files -->
	<link href="assets/css/bootstrap.min.css" rel="stylesheet" />
	<link href="assets/css/material-bootstrap-wizard.css" rel="stylesheet" />

	<!-- CSS Just for demo purpose, don't include it in your project -->
	<link href="assets/css/demo.css" rel="stylesheet" />
	
	<!-- biblioteca moment-->
	<script src="moment.js"></script>
</head>

<body>
	<div class="image-container set-full-height" style="background-image: url('assets/img/wizard-city.jpg')">
	    <!--   Creative Tim Branding   -->
		<!--
	    <a href="http://creative-tim.com">
	         <div class="logo-container">
	            <div class="logo">
	                <img src="assets/img/new_logo.png">
	            </div>
	            <div class="brand">
	                Creative Tim
	            </div>
	        </div>
	    </a>

		-->



	    <!--   Big container   -->
	    <div class="container">
	        <div class="row">
		        <div class="col-sm-8 col-sm-offset-2">
		            <!--      Wizard container        -->
		            <div class="wizard-container">
		                <div class="card wizard-card" data-color="purple" id="wizard">
			                <form action="cronograma_salva.php" method="POST">
			                <!--        You can switch " data-color="rose" "  with one of the next bright colors: "blue", "green", "orange", "purple"        -->

		                    	<div class="wizard-header">
		                        	<h3 class="wizard-title">
		                        		Solicitação de vistoria
		                        	</h3>
									<h5>Vistoria de entrada</h5>
									<input type="hidden" id="tipo_vistoria_id"  name="tipo_vistoria_id" value="1"/>
		                    	</div>
								<div class="wizard-navigation">
									<ul>
			                            <li><a href="#basic" data-toggle="tab">Dados básicos</a></li>
			                            <li><a id="nav_complementar" href="#complementar" data-toggle="tab">Dados complementares</a></li>
			                            <li><a id="nav_sobre_datas" href="#sobre_datas" data-toggle="tab">Definir datas</a></li>
			                        </ul>
								</div>

		                        <div class="tab-content">
		                            <div class="tab-pane" id="basic">
		                            	<div class="row">
		                                	<div class="col-sm-12">
		                                    	<h4 class="info-text"> Vamos iniciar com os detalhes básicos</h4>
		                                	</div>
		                                	<div class="col-sm-5 col-sm-offset-1">
		                                    	<div class="form-group label-floating">
		                                        	<label>COD</label>
		                                        	<input type="text" class="form-control" name="contrato_cod" id="contrato_cod">
													<span id="errorContratoCod"></span>
		                                    	</div>
		                                	</div>
		                            	</div>
		                            </div>
		                            <div class="tab-pane" id="sobre_datas">
		                                <h4 class="info-text">Sobre o Agendamento </h4>
		                                <div class="row">
		                                    <div class="col-sm-5 col-sm-offset-1">
		                                      <div class="form-group label-floating">
		                                        	<label>Imóvel já esta disponivel para vistoria?</label>
		                                        	<select class="form-control" name="imovel_disponivel" id="imovel_disponivel">
		                                            	<option disabled="" selected=""></option>
		                                            	<option value="1">Sim</option>
		                                            	<option value="0">Não </option>
		                                        	</select>
		                                    	</div>
		                                    </div>
		                                	<div class="col-sm-5 col-sm-offset-1" id="q_motivo">
		                                    	<div class="form-group label-floating">
		                                        	<label>Motivo</label>
		                                        	<input type="text" class="form-control" name="disponibilidade_motivo" id="disponibilidade_motivo">
		                                    	</div>
		                                	</div>
		                                	<div class="col-sm-5 col-sm-offset-1" id="q_prazo_inicio">
		                                    	<div class="form-group label-floating">
		                                        	<label>Disponivel a partir de</label>
		                                        	<input type="date" class="form-control" name="prazo_inicio" id="prazo_inicio">
		                                    	</div>
		                                	</div>
											
		                                    <div class="col-sm-4">
		                                    	<div class="form-group label-floating">
		                                            <label>Prazo:</label>
		                                            <p id="lab_prazo">00/00/0000 00:00</p>
		                                        </div>
		                                    </div>
		                                </div>
		                            </div>
		                            <div class="tab-pane" id="complementar">
		                                <div class="row">
		                                    <h4 class="info-text">Nos de uma pequena descrição:</h4>
		                                    <div class="col-sm-5">
		                                    	<div class="form-group label-floating">
		                                        	<label class="control-label">Local da chave</label>
		                                        	<select class="form-control" name="ch_local" id="ch_local">
		                                            	<option disabled="" selected=""></option>
		                                            	<option value="gaveta">gaveta</option>
		                                            	<option value="quadro">quadro</option>
		                                            	<option value="portaria">portaria</option>
		                                        	</select>
		                                    	</div>
		                                    </div>
											
		                                    <div class="col-sm-5">
		                                    	<div class="form-group label-floating">
		                                        	<label class="control-label">Padrão Vistoria</label>
		                                        	<select class="form-control" name="padrao_vistoria" id="padrao_vistoria">
		                                            	<option disabled="" selected=""></option>
		                                            	<option value="digital">Digital</option>
		                                            	<option value="fisico">Fisico</option>
		                                        	</select>
		                                    	</div>
		                                    </div>

											<div class="col-sm-5">
		                                    	<div class="form-group label-floating">
		                                        	<label class="control-label">Imóvel Mobiliado?</label>
		                                        	<select class="form-control" name="imovel_mobiliado" id="imovel_mobiliado">
		                                            	<option disabled="" selected=""></option>
		                                            	<option value="nao">Não</option>
		                                            	<option value="semi">Semi (Ex: apenas armarios, guarda roupas, afins)</option>
		                                            	<option value="mobiliado">Sim (possui mobilia em todo o imóvel. Ex: cama, mesas e outros)</option>
		                                        	</select>
		                                    	</div>
		                                    </div>

											<div class="col-sm-5">
		                                    	<div class="form-group label-floating">
		                                        	<label class="control-label">Controle Portão eletrônico:</label>
		                                        	<select class="form-control" name="ch_qtd_controle" id="ch_qtd_controle">
		                                            	<option disabled="" selected=""></option>
		                                            	<option value="0">Não possui</option>
		                                            	<option value="1">1</option>
		                                            	<option value="2">2</option>
		                                            	<option value="3">3</option>
		                                            	<option value="4">4</option>
		                                            	<option value="5">5</option>
		                                            	<option value="6">6</option>
		                                        	</select>
		                                    	</div>
		                                    </div>

											<div class="col-sm-5">
		                                    	<div class="form-group label-floating">
		                                        	<label class="control-label">Cartão de acesso:</label>
		                                        	<select class="form-control" name="ch_qtd_cartao" id="ch_qtd_cartao">
		                                            	<option disabled="" selected=""></option>
		                                            	<option value="0">Não possui</option>
		                                            	<option value="1">1</option>
		                                            	<option value="2">2</option>
		                                            	<option value="3">3</option>
		                                            	<option value="4">4</option>
		                                            	<option value="5">5</option>
		                                            	<option value="6">6</option>
		                                        	</select>
		                                    	</div>
		                                    </div>
											
											<div class="col-sm-5">
		                                    	<div class="form-group label-floating">
		                                        	<label class="control-label">Tag de acesso:</label>
		                                        	<select class="form-control" name="ch_qtd_tag" id="ch_qtd_tag">
		                                            	<option disabled="" selected=""></option>
		                                            	<option value="0">Não possui</option>
		                                            	<option value="1">1</option>
		                                            	<option value="2">2</option>
		                                            	<option value="3">3</option>
		                                            	<option value="4">4</option>
		                                            	<option value="5">5</option>
		                                            	<option value="6">6</option>
		                                        	</select>
		                                    	</div>
		                                    </div>

											
											<div class="col-sm-5">
		                                    	<div class="form-group label-floating">
		                                        	<label class="control-label">Chave de correio:</label>
		                                        	<select class="form-control" name="ch_qtd_correio" id="ch_qtd_correio">
		                                            	<option disabled="" selected=""></option>
		                                            	<option value="0">Não possui</option>
		                                            	<option value="1">1</option>
		                                            	<option value="2">2</option>
		                                            	<option value="3">3</option>
		                                            	<option value="4">4</option>
		                                            	<option value="5">5</option>
		                                            	<option value="6">6</option>
		                                        	</select>
		                                    	</div>
		                                    </div>

											
											<div class="col-sm-5">
		                                    	<div class="form-group label-floating">
		                                        	<label class="control-label">Cartão Carrinho:</label>
		                                        	<select class="form-control" name="ch_qtd_carrinho" id="ch_qtd_carrinho">
		                                            	<option disabled="" selected=""></option>
		                                            	<option value="0">Não possui</option>
		                                            	<option value="1">1</option>
		                                            	<option value="2">2</option>
		                                            	<option value="3">3</option>
		                                            	<option value="4">4</option>
		                                            	<option value="5">5</option>
		                                            	<option value="6">6</option>
		                                        	</select>
		                                    	</div>
		                                    </div>
		                                </div>
		                            </div>
		                        </div>
		                        <div class="wizard-footer">
	                            	<div class="pull-right">
	                                    <input type='button' class='btn btn-next btn-fill btn-primary btn-wd' id="nextBtn" name='next' value='Proximo' disabled />
	                                    <input type='submit' class='btn btn-finish btn-fill btn-primary btn-wd' name='finish' value='Concluir' />
	                                </div>
	                                <div class="pull-left">
	                                    <input type='button' class='btn btn-previous btn-fill btn-default btn-wd' name='previous' value='Previous' />
	                                </div>
		                            <div class="clearfix"></div>
		                        </div>
			                </form>
		                </div>
		            </div> <!-- wizard container -->
		        </div>
	        </div> <!-- row -->
	    </div> <!--  big container -->

	    <div class="footer">
	        <div class="container text-center">
	             @Reparei</a>
	        </div>
	    </div>
	</div>

</body>
	<!--   Core JS Files   -->
	<script src="assets/js/jquery-2.2.4.min.js" type="text/javascript"></script>
	<script src="assets/js/bootstrap.min.js" type="text/javascript"></script>
	<script src="assets/js/jquery.bootstrap.js" type="text/javascript"></script>

	<!--  Plugin for the Wizard -->
	<script src="assets/js/material-bootstrap-wizard.js"></script>

	<!--  More information about jquery.validate here: http://jqueryvalidation.org/	 -->
	<script src="assets/js/jquery.validate.min.js"></script>
	<!-- Importe o plugin de máscara para jQuery -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
	<script>
		$(document).ready(function() {
			
			$("#q_motivo, #q_prazo_inicio").hide();
			// Adicione a máscara ao campo contrato_cod
			$("#contrato_cod").mask("0000-000/09");

			// Adicione a validação ao campo contrato_cod
			$("#contrato_cod").on("input", function() {
						var valor = $(this).val();
						var invalidos = [
							"0000-000",
							"1111-111",
							"2222-222",
							"3333-333",
							"4444-444",
							"5555-555",
							"6666-666",
							"7777-777",
							"8888-888",
							"9999-999"
						];


						if (valor.length >=10) {
							if (invalidos.includes(valor)) {
								$(this).addClass("is-invalid");
								$("#nextBtn").prop("disabled", true);
								$("#errorContratoCod").text("Código inválido. Por favor, insira um código diferente.");
								$("#nav_complementar").prop("disabled", true);
								$("#nav_sobre_datas").prop("disabled", true);
							} else {
								$(this).removeClass("is-invalid");
								$("#nextBtn").prop("disabled", false);
								$("#errorContratoCod").text("");
								$("#nav_complementar").prop("disabled", false);
								$("#nav_sobre_datas").prop("disabled", false);
							}
						} else {
							$(this).removeClass("is-invalid");
							$("#nextBtn").prop("disabled", true);
							$("#errorContratoCod").text("");
							$("#nav_complementar").prop("disabled", true);
							$("#nav_sobre_datas").prop("disabled", true);
						}
					});

					if (window.location.search) {
						var params = new URLSearchParams(window.location.search);
						if (params.has("solicitante")) {
							// Obtém o valor da variável "solicitante"
							var solicitante = params.get("solicitante");
							
							// Atribui o valor ao campo solicitante
							$("#solicitante").val(solicitante);
						}
					}


					  // Verifica o valor do campo imovel_disponivel
						$("#imovel_disponivel").change(function() {
							var valor = $(this).val();

							// Verifica se o valor é igual a 0
							if (valor === "1") {
								// Oculta os campos disponibilidade_motivo e prazo_inicio
								$("#q_motivo, #q_prazo_inicio").hide();

								// Define o valor de disponibilidade_motivo como nulo
								$("#disponibilidade_motivo").val(null);

								// Obtém a data atual e formata como dd/mm/YYYY
								var dataAtual = new Date();
								var dia = String(dataAtual.getDate()).padStart(2, '0');
								var mes = String(dataAtual.getMonth() + 1).padStart(2, '0');
								var ano = dataAtual.getFullYear();
								var dataFormatada = ano + '-' + mes + '-' + dia;

								// Define o valor de prazo_inicio como a data atual formatada
								$("#prazo_inicio").val(dataFormatada);
								var horarioConclusao = calcularHorarioConclusao();
								$('#lab_prazo').text(horarioConclusao); // Exibe o horário de conclusão do serviço no span com ID "lab_prazo"
							} else {
								// Caso contrário, exibe os campos disponibilidade_motivo e prazo_inicio
								$("#q_motivo, #q_prazo_inicio").show();
								
								// Limpa o valor de prazo_inicio
								$("#prazo_inicio").val('');
								$('#lab_prazo').text('00/00/0000 00:00');
							}
						});


			///adicionar prazo
			  // Função para calcular o horário de conclusão
				function calcularHorarioConclusao() {
					var hoje = new Date();
					var prazoInicio = $('#prazo_inicio').val(); // Obtém o valor do input prazo_inicio
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
					while (horasTrabalhadas < 20) { //antes 20
						if(dataAtual.isoWeekday()==6){
							//não conta prazo sabado
							
							dataAtual.add(2, 'day').hour(8).minute(0); // Avança para segunda
							/*
							if(dataAtual.hour() >= 12){
								//sabado apos meio dia
								dataAtual.add(2, 'day').hour(8).minute(0); // Avança para segunda
							}else{
								//sabado antes do meio dia
								dataAtual.add(1, 'hour'); // Avança uma hora
								horasTrabalhadas++; // Incrementa as horas trabalhadas
							}
								*/
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
				$('#prazo_inicio').on('change', function() {
					var horarioConclusao = calcularHorarioConclusao();
					$('#lab_prazo').text(horarioConclusao); // Exibe o horário de conclusão do serviço no span com ID "lab_prazo"
				});

		});
	</script>

</html>
