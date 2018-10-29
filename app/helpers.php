<?php

if (!function_exists('date_string')) {
    function date_string(\Carbon\Carbon $date)
    {
        // TODO: Remover essa lista quando atualizar pro Carbon 2.0 que já tem os dias da semana em pt-BR
        $daysOfWeek = ['domingo', 'segunda-feira', 'terça-feira', 'quarta-feira', 'quinta-feira', 'sexta-feira', 'sábado'];

        return $daysOfWeek[$date->dayOfWeek] . ' ' . $date->format('(d/m) \à\s H:i');
    }
}

if (!function_exists('recurringDates')) {
    function recurringDates($startDate, $endDate, $weekDaysString)
    {
        $weekDays = weekDaysStringToRecurrString($weekDaysString);

        $recurringRule = new \Recurr\Rule();
        $recurringRule->setFreq('WEEKLY');
        $recurringRule->setByDay($weekDays);
        $recurringRule->setStartDate($startDate);
        $recurringRule->setUntil($endDate);

        $transformer = new \Recurr\Transformer\ArrayTransformer();
        $events = $transformer->transform($recurringRule);

        $dates = [];
        foreach ($events as $event) {
            $dates[] = $event->getStart();
        }

        return $dates;
    }
}

if (!function_exists('weekDaysStringToRecurrString')) {
    function weekDaysStringToRecurrString($weekDaysString)
    {
        $weekDaysTable = [
            '0' => 'SU',
            '1' => 'MO',
            '2' => 'TU',
            '3' => 'WE',
            '4' => 'TH',
            '5' => 'FR',
            '6' => 'SA',
            '7' => 'SU',
        ];

        $weekDays = explode(',', $weekDaysString);
        for ($i = 0; $i < count($weekDays); $i++) {
            $number = $weekDays[$i];
            $weekDays[$i] = $weekDaysTable[$number];
        }

        return $weekDays;
    }
}
