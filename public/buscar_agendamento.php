<?php
require_once 'conexao.php';

// Verifica se o ID do agendamento foi fornecido
if (isset($_GET['id'])) {
    $agendamentoId = $_GET['id'];

    // Consulta SQL para selecionar o agendamento com base no ID
    $sql = "SELECT * FROM agendamentos WHERE id = $agendamentoId";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Obtém os dados do agendamento
        $agendamento = $result->fetch_assoc();

        // Retorna os dados do agendamento como resposta JSON
        echo json_encode($agendamento);
    } else {
        // Caso o agendamento não seja encontrado
        echo json_encode(['error' => 'Agendamento não encontrado']);
    }
} else {
    // Caso o ID do agendamento não seja fornecido
    echo json_encode(['error' => 'ID do agendamento não fornecido']);
}

// Fecha a conexão com o banco de dados
$conn->close();
