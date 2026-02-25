<?php
function calc_agendamento($dt_agendamento, $dt_prazo_ini, $dt_prazo_fim, $dt_prazo_dinamico){
    $ag_block=0;
    $dt_hoje=date("Ymd")+0;
    $dt_amanha=$dt_hoje+1;
    $dt_seisdias=$dt_hoje+6;
    $diasem=array('Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sabado');
    $agendamento_futuro=0;
    $vistoria_agendada=false;
    if($dt_agendamento!=null and $dt_agendamento!='0000-00-00 00:00:00'){
        //vistoria agendada
        $vistoria_agendada=true;
        $ag_calc=date("Ymd",strtotime($dt_agendamento))+0;
        $agendamento_futuro=1;
        if($ag_calc == $dt_hoje){
          $agendamento_at='Agendado Hoje, às '.date("H:i",strtotime($dt_agendamento));
          $agendamento_futuro=0;
        }else if($ag_calc == $dt_amanha){
          $agendamento_at='Agendado Amanhã, às '.date("H:i",strtotime($dt_agendamento));
        }else if($ag_calc <= $dt_seisdias){
          $agendamento_at='Agendado para '.$diasem[date("w",strtotime($dt_agendamento))].', às '.date("H:i",strtotime($dt_agendamento));
        }else{
          $agendamento_at='Agendado para '.date("d/m/Y H:i",strtotime($dt_agendamento));
        }
        //agendamento dinamico
        $agendamento_dinamico=$agendamento_at;
      }else if((date("Ymd",strtotime($dt_prazo_ini))+0)>(date("Ymd")+0)){
        //vistoria indisponivel
        $ag_block=1;  
        $ag_calc=date("Ymd",strtotime($dt_prazo_ini))+0;

        if($ag_calc == $dt_amanha){
          $agendamento_at='Disponivel a partir de Amanhã, às '.date("d/m/Y H:i",strtotime($dt_prazo_ini));
        }else if($ag_calc <= $dt_seisdias){
          $agendamento_at='Disponivel a partir de '.$diasem[date("w",strtotime($dt_prazo_ini))].', às '.date("H:i",strtotime($dt_prazo_ini));
        }else{
          $agendamento_at='Disponivel a partir de '.date("d/m/Y H:i",strtotime($dt_prazo_ini));
        }        
        
        //agendamento dinamico
        $agendamento_dinamico=$agendamento_at;
      }else{
        //vistoria livre
        $ag_calc=date("Ymd",strtotime($dt_prazo_fim))+0;
        if($ag_calc == $dt_hoje){
          $agendamento_at='Prazo até Hoje, às '.date("H:i",strtotime($dt_prazo_fim));
        }else if($ag_calc == $dt_amanha){
          $agendamento_at='Prazo até Amanhã, às '.date("H:i",strtotime($dt_prazo_fim));
        }else if($ag_calc <= $dt_seisdias){
          $agendamento_at='Prazo até '.$diasem[date("w",strtotime($dt_prazo_fim))].', às '.date("H:i",strtotime($dt_prazo_fim));
        }else{
          $agendamento_at='Prazo até '.date("d/m/Y H:i",strtotime($dt_prazo_fim));
        }

        //agendamento dinamico
        $ag_calc=date("Ymd",strtotime($dt_prazo_dinamico))+0;
        if($ag_calc == $dt_hoje){
          $agendamento_dinamico='Prazo até Hoje, às '.date("H:i",strtotime($dt_prazo_dinamico));
        }else if($ag_calc == $dt_amanha){
          $agendamento_dinamico='Prazo até Amanhã, às '.date("H:i",strtotime($dt_prazo_dinamico));
        }else if($ag_calc <= $dt_seisdias){
          $agendamento_dinamico='Prazo até '.$diasem[date("w",strtotime($dt_prazo_dinamico))].', às '.date("H:i",strtotime($dt_prazo_dinamico));
        }else{
          $agendamento_dinamico='Prazo até '.date("d/m/Y H:i",strtotime($dt_prazo_dinamico));
        }


      }
      $retorno['ag_block']=$ag_block;
      $retorno['agendamento_at']=$agendamento_at;
      $retorno['agendamento_dinamico']=$agendamento_dinamico;
      $retorno['agendamento_futuro']=$agendamento_futuro;
      $retorno['vistoria_agendada']=$vistoria_agendada;

      return $retorno;
}