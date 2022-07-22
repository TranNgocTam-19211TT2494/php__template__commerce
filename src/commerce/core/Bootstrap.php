<?php
/**
 * @author Tran Ngoc Tam <ngoctam2303001@gmail.com>
 * @var: work experience
 * */ 

namespace Commerce\Core;

use Dotenv\Dotenv;
use Commerce\Core\Exception\Authorize;
use Commerce\Core\Exception\BadRequest;
use Commerce\Core\Exception\Error;
use Commerce\Core\Exception\ErrorApi;
use Commerce\Core\Exception\Notfound;
use Commerce\Core\Exception\NotfoundApi;

/**
 * Class Bootstrap
 * @package Commerce\Core
 * @access public
 * */ 
class Bootstrap 
{
    /**
     * Registers Core_Autoloader as an SPL autoloader.
     * */ 
    public static function register() 
    {
        if(!Config::getEnv('APP_DEBUG')) {
            ini_set('display_errors', 0);
            error_reporting(E_ALL & ~E_NOTICE);
        } else {
            ini_set('display_errors', 1);
            error_reporting(E_ALL & ~E_NOTICE);
        }
        date_default_timezone_set('Asia/Ho_Chi_Minh');

        //Get configuration file
        // $environment = Dotenv::create(Config::getBasePath());
        // $environment->load();

    }

    public static function dispatch($dir)
    {
        try {
            self::register();

            //Affiliate Filter
            // Affiliate::filter();

            $request_path = isset($_SERVER['PATH_INFO']) ? trim($_SERVER['PATH_INFO'], "/") : "";

            //If there is a controller class file, include it and display an error if no action is taken
            try {
                //Set Url to env
                self::setUrl($dir);

                //Determination of each module controller and action
                $router = new Router($request_path);

                //Execute request
                if (!class_exists($router->controller)) {
                    throw new Notfound("404 Not Found", "404");
                }
                $page = new $router->controller($router->store_id);
                if (method_exists($page, $router->action) && $page->isPermitted($router->action)) {
                    call_user_func_array([$page, $router->action], $router->parameter);
                } else {
                    throw new Notfound("404 Not Found", "404");
                }
            } catch (Notfound $e) {
                $page = new \Commerce\App\Controller\Error($router->store_id);
                // $page->not_found($e->getMessage());
            } catch (NotfoundApi $e) {
                $page = new \Commerce\App\Controller\Error();
                $page->api_notfound_error("404 Not Found");
            } catch (Authorize $e) {
                $page = new \Commerce\App\Controller\Error();
                $page->api_authorize_error();
            } catch (ErrorApi $e) {
                if (Config::getEnv('APP_DEBUG')) {
                    $error = $e->getMessage() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getLine() . PHP_EOL . $e->getTraceAsString();
                    echo str_replace(PHP_EOL, '<br>', $error);
                } else {
                    $page = new \Commerce\App\Controller\Error();
                    $page->api_server_error($e->getMessage());
                }
            } catch (Error $e) {
                if (Config::getEnv('APP_DEBUG')) {
                    $error = $e->getMessage() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getLine() . PHP_EOL . $e->getTraceAsString();
                    echo str_replace(PHP_EOL, '<br>', $error);
                } else {
                    $page = new \Commerce\App\Controller\Error();
                    // $page->server_error();
                }
            } catch (BadRequest $e) {
                if (Config::getEnv('APP_DEBUG')) {
                    $error = $e->getMessage() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getLine() . PHP_EOL . $e->getTraceAsString();
                    echo str_replace(PHP_EOL, '<br>', $error);
                } else {
                    $page = new \Commerce\App\Controller\Error();
                    $page->api_bad_request_error($e->getMessage());
                }
            }
            if (!$page->is_already_output) {
                echo $page->output;
            }
        } catch (\Throwable $e) {
            $error = $e->getMessage() . PHP_EOL . $e->getFile() . PHP_EOL . $e->getLine() . PHP_EOL . $e->getTraceAsString();
            if (Config::getEnv('APP_DEBUG')) {
                echo str_replace(PHP_EOL, '<br>', $error);
            } else {
                $page = new \Commerce\App\Controller\Error();
                // $page->server_error();
                echo $page->output;
            }
            // Logger::log('fatal', $error, 'error');
        }
        exit;
    }

    /**
     * Set Http and https
     * Not required when setting to .env
     * @param $dir
     */
    private static function setUrl($dir)
    {

        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
        $port = isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null;
        $http = "http://{$host}";
        if (Config::getEnv('APP_MODE') != 'local') {
            $https = "https://{$host}";
        } else {
            $https = $http;
        }

        $public_root = rtrim(Config::getPublicDir(), '/');
        $context = trim(str_replace($public_root, '', $dir), '/');
        $context = "/{$context}";

        putenv("HTTP_DOMAIN={$http}");
        putenv("HTTPS_DOMAIN={$https}");
        putenv("CONTEXT={$context}");

    }
}