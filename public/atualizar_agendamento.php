<?php
require_once 'conexao.php';
require_once 'g_ver_login.php';
require_once 'calc_horas.php';



// Verifica se foi enviado o ID do agendamento a ser atualizado
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Consulta SQL para buscar os dados do agendamento antes da atualização
    $sqlSelect = "SELECT * FROM agendamentos WHERE id = $id";
    $resultSelect = $conn->query($sqlSelect);
    
    if ($resultSelect->num_rows > 0) {
        $rowSelect = $resultSelect->fetch_assoc();
        $dataAntiga = json_encode($rowSelect); // Dados antigos em formato JSON
        $dataAntigaArray = $rowSelect; // Dados antigos em formato array
        
        // Atualiza os dados do agendamento
        $contratoCod = $_POST['contrato_cod'];
        $solicitante = $email;
        $imovelDisponivel = $_POST['imovel_disponivel'];
        $disponibilidadeMotivo = $_POST['disponibilidade_motivo'];
        if(isset($_POST['prazo_inicio'])){$prazoInicio = $_POST['prazo_inicio'];}else{$prazoInicio=$dataAntigaArray['prazo_inicio'];}

        //valicação de datas
        if($dataAntigaArray['imovel_disponivel'] == 1 and $imovelDisponivel == 1){
            //se mantem agendamento anterior mantem data
            $prazoInicio=$dataAntigaArray['prazo_inicio'];
            $prazoDinamico=$dataAntigaArray['prazo_dinamico'];
            $prazoFim=$dataAntigaArray['prazo_fim'];
            $prazo_dinamico = $dataAntigaArray['prazo_dinamico'];
        }else{
            //calcula novo przo
            $prazoFim=calcularHorarioConclusao($prazoInicio, 20);
            $prazo_dinamico = calcularHorarioConclusao($prazo_inicio,16);
            $prazoDinamico = $prazo_dinamico;
        }

        // Consulta SQL para atualizar o agendamento
        $sqlUpdate = "UPDATE agendamentos SET contrato_cod = '$contratoCod', imovel_disponivel = '$imovelDisponivel', disponibilidade_motivo = '$disponibilidadeMotivo', prazo_inicio = '$prazoInicio', prazo_fim = '$prazoFim', prazo_dinamico = '$prazo_dinamico' WHERE id = $id";
        $resultUpdate = $conn->query($sqlUpdate);
        
        if ($resultUpdate) {
            // Consulta SQL para buscar os dados atualizados do agendamento
            $sqlSelectUpdated = "SELECT * FROM agendamentos WHERE id = $id";
            $resultSelectUpdated = $conn->query($sqlSelectUpdated);
            
            if ($resultSelectUpdated->num_rows > 0) {
                $rowSelectUpdated = $resultSelectUpdated->fetch_assoc();
                $dataNova = json_encode($rowSelectUpdated); // Dados novos em formato JSON
                
                // Insere o log de alterações na tabela logs_agendamentos
                $camposAlterados = getCamposAlterados($rowSelect, $rowSelectUpdated);
                $dataHora = date('Y-m-d H:i:s');
                
                $sqlLog = "INSERT INTO logs_agendamentos (agendamento_id, data_antiga, data_nova, campos_alterados, data_hora) VALUES ($id, '$dataAntiga', '$dataNova', '$camposAlterados', '$dataHora')";
                $resultLog = $conn->query($sqlLog);
                
                if ($resultLog) {
                    $response = [
                        'success' => true,
                        'data' => [
                            'id' => $id,
                            'solicitante' => $solicitante,
                            'prazo' => $prazoFim,
                            'prazoFormatted' => date('d/m/Y H:i', strtotime($prazoFim)),
                            'prazoDinamico' => $prazoDinamico,
                            'prazoDinamicoFormatted' => date('d/m/Y H:i', strtotime($prazoDinamico)),
                        ]
                    ];
                    echo json_encode($response);
                } else {
                    $response = [
                        'success' => false,
                        'message' => 'Erro ao inserir o log de alterações: ' . $conn->error
                    ];
                    echo json_encode($response);
                }
            } else {
                $response = [
                    'success' => false,
                    'message' => 'Erro ao buscar os dados atualizados do agendamento: ' . $conn->error
                ];
                echo json_encode($response);
            }
        } else {
            $response = [
                'success' => false,
                'message' => 'Erro ao atualizar o agendamento: ' . $conn->error
            ];
            echo json_encode($response);
        }
    } else {
        $response = [
            'success' => false,
            'message' => 'Agendamento não encontrado'
        ];
        echo json_encode($response);
    }
} else {
    $response = [
        'success' => false,
        'message' => 'ID do agendamento não fornecido'
    ];
    echo json_encode($response);
}

// Função para obter os campos alterados
function getCamposAlterados($dataAntiga, $dataNova) {
    $camposAlterados = [];
    
    foreach ($dataNova as $campo => $valor) {
        if ($dataAntiga[$campo] != $valor) {
            $camposAlterados[] = $campo;
        }
    }
    
    return implode(', ', $camposAlterados);
}

// Fecha a conexão com o banco de dados
$conn->close();
?>
