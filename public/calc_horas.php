<?php
function calcularHorarioConclusao($prazoInicio, $h_adicional) {
    $horasTrabalhadas = 0;
    $prazoInicio=$prazoInicio.'';
    
    if (date('Ymd') >= date('Ymd', strtotime($prazoInicio))) {
        //hoje ou antes
        $prazoInicial = date('Y-m-d H:i');
    } else {
        //data futura
        $prazoInicial = date(date('Y-m-d',strtotime($prazoInicio)) . ' 08:00');
    }

    $dataAtual = DateTime::createFromFormat('Y-m-d H:i', $prazoInicial);
    //corrige data atual

    
    //if ($dataAtual->format('N') == 6 and $dataAtual->format('H') >= 12) { 
    if ($dataAtual->format('N') == 6) { 
        $dataAtual->modify('+2 day')->setTime(8, 0);
        // Se for sábado apos meio dia
        //$dataAtual->modify('+2 day')->setTime(8, 0);
    }else if($dataAtual->format('N') == 7){
        $dataAtual->modify('+1 day')->setTime(8, 0);
    }
    


    while ($horasTrabalhadas < $h_adicional) {
        if ($dataAtual->format('N') == 6 and $dataAtual->format('H') >= 12) { 
            // Se for sábado apos meio dia
            $dataAtual->modify('+2 day')->setTime(8, 0);
        } elseif ($dataAtual->format('N') == 7) { 
            // Se for sábado apos meio dia
            $dataAtual->modify('+1 day')->setTime(8, 0);
        } elseif ($dataAtual->format('H') < 8) {
            $dataAtual->setTime(8, 0);
        } elseif ($dataAtual->format('H') >= 12 && $dataAtual->format('H') < 13) {
            $dataAtual->setTime(13, 0);
        } elseif ($dataAtual->format('H') >= 17) {
            $dataAtual->modify('+1 day')->setTime(8, 0);
        } else {
            $dataAtual->modify('+1 hour');
            $horasTrabalhadas++;
        }
    }

    return $dataAtual->format('Y-m-d H:i');
}

?>
