<?php
/**
 * dump & die()
 */
function dd()
{
    echo "<pre>";
    $arguments = func_get_args();
    foreach ($arguments as $argument) {
        print_r($argument);
        echo "<pre>";
    }

    $backtrace = debug_backtrace();
    die($backtrace[0]['file'].':'.$backtrace[0]['line']);
}

function println($string){
    print_r($string. "\n");
}