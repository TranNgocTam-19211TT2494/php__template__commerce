<?php
namespace Commerce\Core;

class Useragent
{
    /**
     * Is it mobile?
     * @var boolean
     */
    public $is_mobile = false;

    /**
     * Whether it is a smartphone
     * @var boolean
     */
    public $is_smart_phone = false;

    /**
     * Is it a crawler?
     * @var boolean
     */
    public $is_crawler = false;

    /**
     * Docomo
     * @var boolean
     */
    public $is_docomo = false;

    /**
     * Au?
     * @var boolean
     */
    public $is_au = false;

    /**
     * Soft bank
     * @var boolean
     */
    public $is_softbank = false;

    /**
     * Is it compatible with Flash?
     * @var boolean
     */
    public $is_flash = false;

    /**
     * Whether it is a 3 g terminal
     * @var boolean
     */
    public $is_3g = false;

    /**
     * Whether cookies can be used
     * @var boolean
     */
    public $is_use_cookie = true;

    /**
     * User agent
     * @var string
     */
    private $_useragent;

    /**
     * instance
     * @var Object Self instance
     */
    static $instance;

    /**
     * Get an instance
     * @return Useragent Object
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Useragent;
        }
        return self::$instance;
    }

    /**
     * constructor
     * @return void
     */
    private function __construct()
    {
        $this->_useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";
        $this->_initialize();
    }


    /**
     * Get the user's IP address.
     */
    public static function get_ipaddress()
    {
        return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
    }

    /**
     * Get the user's IP address.
     */
    public static function get_forward_ipaddress()
    {
        return isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : null;
    }

    /**
     * Check user agents and set their respective flags.
     * @access    public
     * @return    Void
     */
    private function _initialize()
    {
        $this->_is_smart_phone();
        $this->_set_is_crowler();
    }

    private function _is_smart_phone()
    {
        $sp_useragents = array(
            'iPhone', 'iPod', 'Android', 'MSIEMobile',
            'dream', 'CUPCAKE', 'webOS', 'incognito', 'webmate',
            'blackberry9500', 'blackberry9530', 'blackberry9520', 'blackberry9550', 'blackberry9800'
        );
        $pattern = '/' . implode('|', $sp_useragents) . '/i';
        $this->is_smart_phone = preg_match($pattern, $this->_useragent);
    }

    /**
     * Crawler judgment
     * @return    bool    true:Crawler, false:Non-crawler
     */
    private function _set_is_crowler()
    {
        $crawler_arr = array(
            'Googlebot-Mobile', 'Y!J-SRD', 'Y!J-MRD',
            'moba-crawler', 'mobile goo', 'LD_mobile_bot', 'froute.jp'
        );

        foreach ($crawler_arr as $val) {
            if (false !== strpos($this->_useragent, $val)) {
                $this->is_crawler = true;
                break;
            }
        }
    }
}