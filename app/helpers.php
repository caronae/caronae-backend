<?php

if (!function_exists('date_string')) {
    function date_string(\Carbon\Carbon $date)
    {
        // TODO: Remover essa lista quando atualizar pro Carbon 2.0 que já tem os dias da semana em pt-BR
        $daysOfWeek = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];
        return $daysOfWeek[$date->dayOfWeek] . ' ' . $date->format('(d/m) \à\s H:i');
    }
}