<?php

if (! function_exists('format_php')) {
    function format_php($value)
    {
        $formatter = new NumberFormatter('en_PH', NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($value, 'PHP');
    }
}
