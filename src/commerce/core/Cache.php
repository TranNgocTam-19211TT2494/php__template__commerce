<?php
namespace Commerce\Core;

class Cache
{
    private static $_data = null;
    public static function set($key, $val, $duration = null)
    {
        $key = sha1($key);
        self::_setDataForFile($key, $val, $duration);
        unset(self::$_data[$key]);
    }

    public static function get($key)
    {
        $key = sha1($key);
        $result = null;
        if (isset(self::$_data[$key])) {
            return self::$_data[$key];
        }
        $result = self::_getDataForFile($key);
        if ($result) {
            self::$_data[$key] = $result;
        }
        return $result;
    }

    public static function clear($key)
    {
        $key = sha1($key);
        $result = self::_clearDataForFile($key);
        unset(self::$_data[$key]);
        return $result;
    }

    private static function _setDataForFile($key, $val)
    {
        $file = Config::getBasePath() . "data/cache/store/" . $key;
        $data = serialize($val);
        File::write($file, $data);
    }

    private static function _getDataForFile($key)
    {
        $file = Config::getBasePath() . "data/cache/store/" . $key;
        if (!file_exists($file)) {
            return false;
        }
        $data = File::read($file);
        return ($data) ? unserialize($data) : null;
    }

    private static function _clearDataForFile($key)
    {
        $file = Config::getBasePath() . 'data/cache/store/' . $key;
        unlink($file);
        return null;
    }
}