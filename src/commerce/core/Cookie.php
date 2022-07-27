<?php
namespace Commerce\Core;

class Cookie
{
    public static function put($key, $val, $json = true, $lifetime = 2419200)
    {
        $val = ($json) ? json_encode($val) : $val;
        setcookie($key, $val, time() + $lifetime, '/', '', true, true);
    }

    public static function get($key, $default = "", $json = true)
    {
        $val = isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
        $val = ($val) ?: $default;
        return ($json) ? json_decode($val, true) : $val;
    }

    public static function clear($key)
    {
        setcookie($key, '', time() - 2419200, '/');
    }
}