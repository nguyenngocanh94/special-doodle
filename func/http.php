<?php
/**
 * all functions that relevant to http request
 * as -> header, form, post ...
 */


function headers(): array
{
    $headers = array();
    foreach($_SERVER as $key => $value) {
        if (substr($key, 0, 5) <> 'HTTP_') {
            continue;
        }
        $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
        $headers[$header] = $value;
    }
    return $headers;
}

function get_body() : array{
    return json_decode(file_get_contents('php://input'), true);
}


function get_post(): array
{
    return $_POST;
}


function get_query(): array
{
    return $_GET;
}

function get_ip(){
    return $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'];
}