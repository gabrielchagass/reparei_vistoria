<?php
require_once 'conexao.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reparei - Vistorias</title>
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
	<!-- biblioteca moment-->
	<script src="moment.js"></script>

  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            margin-top: 50px;
        }

    .blink {
      color:#f00;
    }
  </style>
    </style>	
</head>
<body>
  
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" href="#"></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item active">
          <a class="nav-link" href="#">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="nova_solicitacao.php">Vistoria Entrada</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="nova_solicitacao_saida.php">Vistoria Saida</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="nova_solicitacao_fotos.php">Vistoria de Captação</a>
        </li>
      </ul>
    </div>
  </nav>

  <div class="container mt-5">
    <?php

        // Consulta SQL para confirmar qual o cargo
        $cargo='Atendimento';
        $sql = "SELECT * FROM cargos WHERE email = \"".$email."\" ORDER BY id ASC";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $cargo=$row['cargo'];
            }
        }

        if(strpos($cargo,'Atendimento') !== false or strpos($cargo,'guest') !== false){
          $filtro=' AND solicitante = \''.$email.'\'';
          include('lista_agendamentos.php');
        }else if(strpos($cargo,'gerente_cml') !== false){
          $filtro=' AND tipo_vistoria_id = 1';
          include('lista_agendamentos.php');
        }else if(strpos($cargo,'gerente_adm') !== false){
          $filtro=' AND tipo_vistoria_id = 2';
          include('lista_agendamentos.php');
        }else if(strpos($cargo,'reparos') !== false){
          $filtro='';
          include('lista_painel_reparos.php');
        }else if(strpos($cargo,'cadastro_entrada') !== false){
          $filtro=' AND tipo_vistoria_id = 1';
          include('lista_vistorias_cadastro.php');          
          $filtro=' AND solicitante = \''.$email.'\'';
          include('lista_agendamentos.php');
        }else if(strpos($cargo,'cadastro_saida') !== false){
          $filtro=' AND tipo_vistoria_id = 2';
          include('lista_vistorias_cadastro.php');          
          $filtro=' AND solicitante = \''.$email.'\'';
          include('lista_agendamentos.php');
        }else if(strpos($cargo,'cadastro') !== false){
          $filtro='';
          include('lista_vistorias_cadastro.php');
          
          $filtro=' AND solicitante = \''.$email.'\'';
          include('lista_agendamentos.php');
        }else if(strpos($cargo,'vistoriador') !== false){
          echo '
          <script>
            setTimeout(function(){
                window.location.href = "vistoriador.php";
            }, 0); //
          </script>';
        }
        
    ?>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
