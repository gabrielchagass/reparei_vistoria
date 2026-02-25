<?php

function parseDecimal($number) {
    // Remove espaços em branco
    $number = trim($number);

    $number=str_replace(array('+','-','/',' '),'',$number);

    // Verifica a posição do último ponto e da última vírgula
    $lastCommaPos = strrpos($number, ',');
    $lastDotPos = strrpos($number, '.');

    // Decide o separador de milhares e decimal
    if ($lastCommaPos > $lastDotPos) {
        // A vírgula está depois do ponto, vírgula é decimal
        $number = str_replace('.', '', $number);  // Remove pontos
        $number = str_replace(',', '.', $number); // Troca vírgula por ponto
    } else {
        // O ponto está depois da vírgula ou não há vírgula, ponto é decimal
        if ($lastCommaPos !== false) {
            // Remove as vírgulas se houverem
            $number = str_replace(',', '', $number);
        }
    }
    
    if($number=='' or $number == null){$number = 0;}

    // Converte para float
    return (float) $number;
}
?>