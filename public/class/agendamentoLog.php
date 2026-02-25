<?php
require_once('conexao.php');
require_once 'g_ver_login.php';



class agendamentoLog{
    private $con;
    private int $user;
    private int $agendamento_id;
    private $data_antiga;
    private $data_nova;
    private string  $alteracao_txt;

    public function __construct($con, $user, $agendamento_id, $alteracao_txt, $data_antiga, $data_nova) {
        $this->con = $con;
        $this->user = $user;
        $this->agendamento_id = $agendamento_id;
        $this->alteracao_txt = $alteracao_txt;
        $this->data_antiga = $data_antiga;
        $this->data_nova = $data_nova;
    }

    public function salvar(){
        $dataHora = date('Y-m-d H:i:s');

        //busca campos alterados
        $camposAlterados = $this->getCamposAlterados($this->data_antiga, $this->data_nova);
        $camposAlterados_txt=implode(', ', $camposAlterados);
       
        //filtra variavel somente com oque foi alterado
        $dados_old=[];
        $dados_new=[];
        foreach($camposAlterados as $campo){
            $dados_old[$campo]=$this->data_antiga[$campo];
            $dados_new[$campo]=$this->data_nova[$campo];
        }


        $sqlLog = "INSERT INTO logs_agendamentos 
        (agendamento_id, alteracao_txt, data_antiga, data_nova, campos_alterados, data_hora, user_id) 
        VALUES ($this->agendamento_id, '$this->alteracao_txt', '$this->data_antiga', '$this->data_nova', '$camposAlterados_txt', '$dataHora', '$this->user')";
        $resultLog = $this->con->query($sqlLog);
    }

    
    // Função para obter os campos alterados
    function getCamposAlterados($dataAntiga, $dataNova) {
        $camposAlterados = [];
        
        foreach ($dataNova as $campo => $valor) {
            if ($dataAntiga[$campo] != $valor) {
                $camposAlterados[] = $campo;
            }
        }
        
        return $camposAlterados;
    }
}


?>