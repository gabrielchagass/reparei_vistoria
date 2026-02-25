<?php
$error_page='<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Erro ao Salvar</title>
  <!-- Incluindo os arquivos CSS do Bootstrap -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <!-- Estilos personalizados para a tela de erro -->
  <style>
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .error-container {
      text-align: center;
    }
    .error-code {
      font-size: 4rem;
      font-weight: bold;
      color: #dc3545;
    }
    .error-message {
      font-size: 1.5rem;
      margin-top: 10px;
      color: #dc3545;
    }
    .home-link {
      margin-top: 20px;
    }
  </style>
</head>
<body>

<div class="error-container">
  <div class="error-code"><!--error_title--></div>
  <div class="error-message"><!--error_msg--></div>
  <a href="index.php" class="home-link btn btn-primary mt-3">Voltar à Página Inicial</a>
</div>

</body>
</html>';
?>