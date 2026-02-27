<?php
class vistoria{
    private $cod;
    private $id_agendamento;
    private $id_vistoria;

    public function __construct($cod, $id_agendamento, $id_vistoria) {
        $this->cod = $cod;
        $this->id_agendamento = $id_agendamento;
        $this->id_vistoria = $id_vistoria;
    }

    public function concluir($data) {
        include_once('con_v.php');

        // Normaliza data ISO (ex: 2026-02-26T12:09:28.879+00:00) para formato MySQL
        $dataObj = date_create($data);
        $dataMysql = $dataObj ? $dataObj->format('Y-m-d H:i:s') : null;

        // Consulta SQL para selecionar os registros da tabela "agendamentos" com data_fim nulo
        $sql = "SELECT * FROM agendamentos WHERE devolus_agendamento_id = ".$this->id_agendamento." AND status_id < 4 AND data_fim IS NULL ORDER BY id DESC LIMIT 1";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            //encontrado
            while ($row = $result->fetch_assoc()) {
                $AgendamentoId=$row['id'];
                $DevolusAgendamentoId=$row['devolus_agendamento_id'];
                
                //libera vistoriador
                // Coluna agendamento_id Ã© NOT NULL no banco; use 0 como "sem agendamento" para evitar erro
                $sqlx = "UPDATE cargos SET agendamento_id = 0 WHERE agendamento_id = $AgendamentoId";
                $conn->query($sqlx);
            }
            

            // Preparar o comando SQL
            $sql = "UPDATE agendamentos SET status_id = 4, devolus_vistoria_id = ".$this->id_vistoria.", data_fim = ".($dataMysql ? "'$dataMysql'" : "NULL")." WHERE id = $AgendamentoId";
            // Executar o comando SQL
            if ($conn->query($sql) === TRUE) {
                //echo "Atualização realizada com sucesso!";
                return $AgendamentoId;
            } else {
                //echo "Erro na atualização: " . $conexao->error;
                return false;
            }

        }else{
            //nada encontrado
            return true;
        }




        // Lógica para concluir a vistoria com base no código ($this->cod)
        // ...
        // Retorne o resultado da conclusão da vistoria
        //return "Vistoria concluída com sucesso para o código {$this->cod}";
    }
}

?>
