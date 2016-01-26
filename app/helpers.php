<?php

if(!function_exists('trimText')){
    function trimText($text, $howMuch) {
        return strlen($text) > $howMuch ? substr($text, 0, $howMuch - 3) . '...' : $text;
    }
}