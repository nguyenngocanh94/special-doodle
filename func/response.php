<?php

function send_json($data){
    header("Content-type: application/json");
    echo echo_d($data);
    exit();
}

function echo_d($dataJson) {
    $result = json_encode($dataJson);

    if ($result) {
        return $result;
    }

    return json_encode(filter_values_errors_from_array($dataJson));
}

function ok($data){
    http_response_code(200);
    send_json($data);
}