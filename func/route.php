<?php

use App\Collection\Linq;

function get_handler() : array{
    $requestUri = get_request_uri();
    list($controllerName, $actionName) = Linq::fromStr($requestUri, '/')->filter(function ($path){
        return $path!="";
    })->toArray();

    $controllerFullName = '\App\Controllers\\'.ucfirst($controllerName).'Controller';
    return array((new $controllerFullName()), $actionName);
}

function get_request_uri(){
    $requestUri = $_SERVER['REQUEST_URI'];
    $sep = strpos($requestUri,'?');
    if (is_numeric($sep)){
        return substr($requestUri, 0, $sep);
    }
    return $requestUri;
}