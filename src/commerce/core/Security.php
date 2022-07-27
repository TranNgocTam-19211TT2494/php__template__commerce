<?php
namespace Commerce\Core;

use Commerce\App\Service\Redis;

class Security
{
    /**
     * Generate a one-time token.
     * @param $session
     * @return String
     */
    public static function createCsrf()
    {
        $token_key = rand(0, 99999999) . microtime(true);
        Logger::log("info", "create {$token_key}");
        $token = sha1($token_key);
        Session::put('token', $token);
        return $token;
    }

    /**
     * Returns whether the session token matches the posted token.
     * @param $input
     * @param $session
     * @return boolean
     */
    public static function checkCsrf()
    {
        $input = Request::post('token');
        $session = Session::flushget('token', 'common');
        if ($input !== $session) {
            Logger::log("info", "token unmatch => " . $input . " != " . $session);
            return false;
        }
        return true;
    }

    /**
     * Set the key to prevent double login
     * @param $account_id
     * @param string $type
     * @return string
     */
    public static function setLoginKey($account_id,$type = 'admin')
    {
        $key = Crypter::getRandomHash();
        $login_key = $type . ':account:login:' . $account_id;
        $redis = Redis::getInstance();
        $redis->set($login_key,$key,86400);
        return $key;
    }

    /**
     * Check for double login
     * @param $key
     * @param $account_id
     * @param string $type
     * @return bool
     */
    public static function checkLoginKey($key,$account_id,$type = 'admin')
    {
        $login_key = $type . ':account:login:' . $account_id;
        $redis = Redis::getInstance();
        $saved_account_key = $redis->get($login_key);
        var_dump(($saved_account_key).die);
        $result = ($key == $saved_account_key);
        if($result){
            $redis->expire($login_key,86400);
            return true;
        }
        return false;
    }

    /**
     * Generate a password.
     * Enter description here ...
     */
    public static function generatePassword()
    {
        $string = uniqid(mt_rand(), true);
        return strtolower(substr($string, 0, 5));
    }
}