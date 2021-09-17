<?php

if (!function_exists('prepare_json_columns')) {

    function prepare_json_columns($array, $json_columns)
    {
        foreach ($json_columns as $column) {
            if (isset($array[$column])) {
                $array[$column] = json_encode($array[$column]);
            }
        }
        return $array;
    }
}
