<?php
require_once('conexao.php');
require_once 'g_ver_login.php';

class Consulta{
    private $con;
    public function __construct($con) {
        $this->con = $con;
    }

    public function agendamentos($id){
        //verificar variaveis
        $vistoria_id=$id;
    
        //busca vistoria
        $VISTORIA=array();
        $sql = "SELECT * 
                FROM agendamentos 
                WHERE agendamentos.id = '$vistoria_id'";
        $result = $this->con->query($sql);
        while ($vistoria_at = $result->fetch_assoc()) {
        $VISTORIA=$vistoria_at;
        }
        $VISTORIA["proprietarios"]=array();
        $VISTORIA["inquilinos"]=array();
        $VISTORIA["fiadores"]=array();
    
        //busca proprietário
        $sql = "SELECT * 
                FROM pro_agendamento_cliente
                JOIN clientes ON clientes.id =  pro_agendamento_cliente.id_cliente
                WHERE pro_agendamento_cliente.id_agendamento = '$vistoria_id'";
        $result = $this->con->query($sql);
        while ($pessoa_at = $result->fetch_assoc()) {
            $VISTORIA["proprietarios"][]=$pessoa_at;
        }
    
        //busca inquilino
        $sql = "SELECT * 
                FROM inq_agendamento_cliente
                JOIN clientes ON clientes.id =  inq_agendamento_cliente.id_cliente
                WHERE inq_agendamento_cliente.id_agendamento = '$vistoria_id'";
        $result = $this->con->query($sql);
        while ($pessoa_at = $result->fetch_assoc()) {
            $VISTORIA["inquilinos"][]=$pessoa_at;
        }
    
        //busca fiador
        $sql = "SELECT * 
                FROM fia_agendamento_cliente
                JOIN clientes ON clientes.id =  fia_agendamento_cliente.id_cliente
                WHERE fia_agendamento_cliente.id_agendamento = '$vistoria_id'";
        $result = $this->con->query($sql);
        while ($pessoa_at = $result->fetch_assoc()) {
            $VISTORIA["fiadores"][]=$pessoa_at;
        }
    
        return $VISTORIA;
    }
}



?>