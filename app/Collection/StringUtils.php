<?php

namespace App\Collection;

class StringUtils
{

    public static function subLastString($string , $cutString){
        return substr($string, 0, strlen($string)-strlen($cutString));
    }

    public static function subFisrtString($string , $cutString){
        return substr($string, strlen($cutString), strlen($string)-strlen($cutString));
    }

    /**
     * Translates a camel case string into a string with
     * underscores (e.g. firstName -> first_name)
     *
     * @param string $camel String in camel case format
     * @return string $str Translated into underscore format
     */
    public static function camelToSnake(string $camel){
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camel));
    }

    /**
     * Translates a string with underscores
     * into camel case (e.g. first_name -> firstName)
     *
     * @param string $snake String in underscore format
     * @param bool $capitalise_first_char If true, capitalise the first char in $str
     * @return string $str translated into camel caps
     */
    public static function snakeToCamel(string $snake,bool $capitalise_first_char = false){
        if($capitalise_first_char) {
            $snake[0] = strtoupper($snake[0]);
        }
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $snake))));
    }

    /**
     * Translates a string with underscores
     * into camel case (e.g. first_name -> firstName)
     *
     * @param string $snake String in underscore format
     * @param bool $capitalise_first_char If true, capitalise the first char in $str
     * @return string $str translated into camel caps
     */
    public static function snakeToPascal(string $snake,bool $capitalise_first_char = false){
        return ucfirst(self::snakeToCamel($snake,$capitalise_first_char));
    }

    /**
     *  
     * @param $string
     * @return string
     */
    public static function trimAll($string){
        $string = str_replace(' ','',$string);
        return trim($string);
    }

    public static function isFirstUpcase($str){
        return ctype_upper($str[0]);
    }

    public static function reverse($str){
        preg_match_all('/./us', $str, $ar);
        return join('', array_reverse($ar[0]));
    }

    static function random($length): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}