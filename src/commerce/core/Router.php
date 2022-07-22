<?php
/**
 * @author Tran Ngoc Tam <ngoctam2303001@gmail.com>
 * @var: work experience
 * */ 
namespace Commerce\Core;

use Commerce\App\Service\Router as AppRouter;
/**
 * Class Router
 * @package Commerce\Core
 * @access public
 * */ 
class Router 
{
    public $store_id;
    public $controller;
    public $action;
    public $parameter = [];

    /**
     * @param $url
     * @throws Exception\Notfound
     */
    public function __construct($url = "")
    {
        #code
    }
    /**
     * pathをclassにroutingする
     *
     * @param array $segments
     * @return null
     */
    private function _map($segments)
    {
        $this->controller = "\Commerce\App\Controller";
        while ($segments) {
            if (empty($segments[0])) {
                break;
            }
            $this->controller .= "\\" . ucfirst($segments[0]);
            if (class_exists($this->controller)) {
                $segments = array_slice($segments, 1);
                $this->action = isset($segments[0]) ? $segments[0] : null;
                $segments = array_slice($segments, 1);
                $this->parameter = isset($segments[0]) ? $segments : [];
                break;
            }
            $segments = array_slice($segments, 1);
        }
        if (self::_isDirectoryPath($this->controller)) {
            $this->controller .= "\Index";
        }
        $this->action = ($this->action) ? $this->action : 'index';
    }

    /**
     * Whether the requested controller is the root of a directory
     *
     * @param string $controller
     * @return bool
     */
    private static function _isDirectoryPath($controller)
    {

        $file = __DIR__ . "/../../" . trim(str_replace('\\', '/', $controller),'/');
        $file = str_replace("/Commerce/App/","/commerce/app/", $file);
        return is_dir($file);
    }

    /**
     * Split the URL with a slash.
     *
     * @param string $uri
     * @return array
     */
    private static function _divideUri($uri)
    {
        $divided = explode("/", $uri);
        return $divided;
    }

    /**
     * Customized routing
     * @param $path
     * @return bool
     */
    
}