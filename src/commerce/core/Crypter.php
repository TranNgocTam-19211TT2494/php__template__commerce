<?php
namespace Commerce\Core;

class Crypter
{
    /**
     * Irreversible encryption process : 2-stage encryption
     * @param $text 
     * @return String
     */
    public static function encryptSha1($text)
    {
        $key = null;
        if (strlen($text) > 2) {
            $key = substr($text, 0, 2);
        }
        return sha1($text . ':' . $key);
    }
    /**
     * Random number key generate
     * @return string
     */
    public static function getRandomHash($key = null)
    {
        $key = ($key) ?: Config::getEnv('SYSTEM_HASH_KEY');
        $time = microtime(true);
        $str = $time . $key;
        return sha1($str);
    }
    /**
     * encrypt Open ssl
     * @param $data
     * @param $iv
     * @return string
     */
    public static function encryptOpenSSL($data, $iv)
    {
        if(!Config::getEnv('SYSTEM_PRIVATE_DATA_ENCRYPT')){
            return $data;
        }
        if(!$iv){
            return $data;
        }
        $method = Config::getEnv('SYSTEM_PRIVATE_DATA_ENCRYPT_METHOD');
        $password = Config::getEnv('SYSTEM_PRIVATE_DATA_SECRET');
        $iv = hex2bin($iv);
        return bin2hex(openssl_encrypt($data, $method, $password, OPENSSL_RAW_DATA, $iv));
    }
    public static function decryptOpenSSL($data, $iv)
    {
        if(!Config::getEnv('SYSTEM_PRIVATE_DATA_ENCRYPT')){
            return $data;
        }
        if(!$iv){
            return $data;
        }
        $method = Config::getEnv('SYSTEM_PRIVATE_DATA_ENCRYPT_METHOD');
        $password = Config::getEnv('SYSTEM_PRIVATE_DATA_SECRET');
        $iv = hex2bin($iv);
        return openssl_decrypt(hex2bin($data), $method, $password, OPENSSL_RAW_DATA, $iv);
    }
    /**
     * Get initial vector
     * @return false|string
     */
    public static function getOpenSSLInitialVector()
    {
        $method = Config::getEnv('SYSTEM_PRIVATE_DATA_ENCRYPT_METHOD');
        $iv_length = openssl_cipher_iv_length($method);
        return bin2hex(openssl_random_pseudo_bytes($iv_length));
    }
}