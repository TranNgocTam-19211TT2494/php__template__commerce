<?php
namespace Commerce\Core;

class Session 
{
    private static $has_start = false;

    // constructor
    public static function start()
    {
        if (self::$has_start === false) {
            $name = Config::getEnv('SESSION_NAME');
            session_name($name);
            session_start();
            self::$has_start = true;
        }
    }

    /**
     * Store the session.
     * @access    public
     * @param String $var
     * @param mixed $val
     * @return    Void
     */
    public static function put($var, $val)
    {
        if ($var == null) {
            $_SESSION[Config::getEnv('SESSION_PREFIX')] = $val;
        } else {
            $_SESSION[Config::getEnv('SESSION_PREFIX')][$var] = $val;
        }
    }

    /**
     * Get the value from the session.
     * If no arguments are specified, it returns all the values ​​in the namespace.
     * @access    public
     * @param String $var
     * @return    mixed
     */
    public static function get($var = null, $default = null)
    {
        if (isset($var)) {
            return isset($_SESSION[Config::getEnv('SESSION_PREFIX')][$var]) ? $_SESSION[Config::getEnv('SESSION_PREFIX')][$var] : $default;
        } else {
            return isset($_SESSION[Config::getEnv('SESSION_PREFIX')]) ? $_SESSION[Config::getEnv('SESSION_PREFIX')] : $default;
        }
    }

    /**
     * Gets the value from and deletes the session
     * @access    public
     * @param String $var
     * @return    mixed
     */
    public static function flushget($var = null, $default = null)
    {
        if (isset($var)) {
            $result = isset($_SESSION[Config::getEnv('SESSION_PREFIX')][$var]) ? $_SESSION[Config::getEnv('SESSION_PREFIX')][$var] : $default;
            self::clear($var);
            return $result;
        }
    }

    /**
     * Delete the session value
     * @access    public
     * @param String $var
     * @return    Void
     */
    public static function clear($var = null)
    {
        if ($var !== null && $var !== "") {
            unset($_SESSION[Config::getEnv('SESSION_PREFIX')][$var]);
        } else {
            unset($_SESSION[Config::getEnv('SESSION_PREFIX')]);
        }
    }

    /**
     * End writing session
     * @access  public
     * @return  void
     */
    public static function close()
    {
        self::$has_start = false;
        session_write_close();
    }
}