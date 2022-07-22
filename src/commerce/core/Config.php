<?php
/**
 * @author Tran Ngoc Tam <ngoctam2303001@gmail.com>
 * @var: work experience
 * */ 
namespace Commerce\Core;

/**
 * Class Config
 * @package Commerce\Core
 * @access public
 * */ 
class Config 
{
    public static function getEnv($key) 
    {
        $value = getenv($key);
        $value = (strtolower($value) === 'true') ? true : $value;
        $value = (strtolower($value) === 'false') ? false : $value;
        return $value;
    }

    /**
     * Return the application base
     * @return string
     */
    public static function getBasePath()
    {
        return realpath(dirname(__FILE__) . "../../../");
    }

    /**
     * Return system base
     * @return string
     */
    public static function getSystemBase()
    {
        return self::getBasePath() . "/src/commerce";
    }

    /**
     * PublicDir return it
     * @return string
     */
    public static function getPublicDir()
    {
        return self::getBasePath() . "/htdocs/";
    }

    /**
     * Public Return the base
     * @return string
     */
    public static function getPublicBase()
    {
        return self::getBasePath() . "/htdocs/" . self::getEnv('CONTEXT') . "/";
    }

    /**
     * Cron Return the base
     * @return string
     */
    public static function getCronBase()
    {
        return self::getBasePath() . "/cron/";
    }

    /**
     * Return the application base
     * @return string
     */
    public static function getAppBase()
    {
        return self::getSystemBase() . "app/";
    }

    /**
     * Return template base
     * @return string
     */
    public static function getTemplateBase()
    {
        return self::getSystemBase() . "template/";
    }

    /**
     * Return cache base
     * @return string
     */
    public static function getTemplateCacheBase()
    {
        return self::getBasePath() . "/data/cache/template/";
    }

    /**
     * Return cache base
     * @return string
     */
    public static function getCacheBase()
    {
        return self::getBasePath() . "/data/cache/";
    }
    
    /**
     * Return a temporary file base
     * @return string
     */
    public static function getTmpBase()
    {
        $path = self::getBasePath() . "/data/tmp/";
        return $path;
    }

    /**
     * Return log file base
     * @return string
     */
    public static function getLogBase()
    {
        $path = self::getBasePath() . "/data/logs/";
        return $path;
    }

    /**
     * Returns font base
     * @return string
     */
    public static function getFontBase()
    {   
        $path = self::getBasePath() . "/data/fonts/";
        return $path;
    }

}