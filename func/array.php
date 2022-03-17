<?php

function flatten($array): array
{
    $return = array();
    foreach ($array as $key => $value) {
        if (is_array($value)){
            $return = array_merge($return, self::flatten($value));
        } else {
            $return[$key] = $value;
        }
    }

    return $return;
}

function is_assoc(array $arr) : bool
{
    if (array() === $arr) return false;
    return array_keys($arr) !== range(0, count($arr) - 1);
}

function filter_values_errors_from_array($data): array
{
    if (!is_array($data)) {
        return [];
    }

    $result = [];

    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $value = filter_values_errors_from_array($value);
        }

        if (is_nan($value)) {
            continue;
        }
        $result[$key] = $value;
    }

    return $result;
}