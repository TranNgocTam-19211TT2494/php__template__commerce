<?php
namespace Commerce\Core;

use Commerce\App\Utils\Format;

class Request
{
    /**
     * Get the value of Get
     * @param $var
     * @param null $default
     * @return String|null
     */
    public static function get($var, $default = null)
    {
        return isset($_GET[$var]) ? self::convertEncoding($_GET[$var]) : $default;
    }

    /**
     * Get the value of Post
     * @param $var
     * @param null $default
     * @return array|string|null
     */
    public static function post($var, $default = null)
    {
        return isset($_POST[$var]) ? self::convertEncodingArray($_POST[$var]) : $default;
    }

    /**
     * Determine if the request is get
     * @return bool
     */
    public static function isGet()
    {
        if ($_SERVER['REQUEST_METHOD'] == "GET") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Determine if the request is post
     * @return bool
     */
    public static function isPost()
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Convert string encoding
     * @param $string
     * @return string
     */
    public static function convertEncoding($string)
    {
        $string = mb_convert_kana($string, 'KVa', 'UTF-8');
        $string = Format::replaceReturn($string);
        return trim($string);
    }

    /**
     * Encode an array at once
     * @param $data
     * @return array|string
     */
    public static function convertEncodingArray($data)
    {
        if (!is_array($data)) {
            return self::convertEncoding($data);
        }
        foreach ($data as $key => $val) {
            if (is_array($val)) {
                $data[$key] = self::convertEncodingArray($val);
            } else {
                $data[$key] = self::convertEncoding($val);
            }
        }
        return $data;
    }
}