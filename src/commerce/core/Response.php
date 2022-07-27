<?php


namespace Commerce\Core;

use Twig;
use Commerce\App\Helper\Extension\Filter;
use Commerce\Core\Exception\Error;

class Response
{

    private $context = [];
    private $twig;


    /**
     * Response constructor.
     * @param bool $is_create_token
     */
    public function __construct($is_create_token = false)
    {
        $this->context = [];

        //デフォルトでコンテキストにセット
        $this->set("messages", Session::flushget("messages"));
        $this->set("errors", Session::flushget("errors"));
        if ($is_create_token) {
            $this->set("token", Security::createCsrf());
        }
    }

    /**
     * Redirect to the specified url
     * @param string $uri
     * @param int $http_code
     */
    public static function redirectTo($uri = "", $http_code = 302)
    {
        header("Location: {$uri}", true, $http_code);
        exit;
    }

    /**
     * Output Csv
     * @param string $data
     * @param string $filename
     * @param bool $encode
     */
    public static function outputCsv($data, $filename, $encode = true)
    {
        header("Content-disposition: attachment; filename=$filename");
        header("Content-type: application/octet-stream; name=$filename");
        if ($encode) {
            echo mb_convert_encoding($data, "SJIS-win", "UTF-8");
        } else {
            echo $data;
        }
        exit;
    }

    /**
     * Set the value for the template
     * @param $name
     * @param $value
     * @return void
     */
    public function set($name, $value = null)
    {
        $this->context[$name] = $value;
    }

    /**
     * Output View template.
     * @param string $path
     * @return string
     * @throws Error
     */
    public function render($path)
    {
        $header_string = 'Content-Type: text/html; charset=utf-8';
        $this->context['character'] = "UTF-8";
        $result = self::template($path . '.html', $this->context);
        header($header_string);
        return $result;
    }

    /**
     * Output template for email.
     * @param string $path
     * @param array $params
     * @return string
     * @throws Error
     */
    public static function renderMailTemplate($path, $params = [])
    {
        return self::template('mail/' . $path . '.txt', $params);
    }

    /**
     * Draw template
     * @param string $path
     * @param array $params
     * @return string
     * @throws Error
     */
    public static function template($path, $params = [])
    {
        $result = "";
        $twig = self::_prepareTwigTemplate();
        try {
            $template = $twig->load($path);
            $result = $template->render($params);
        } catch (Twig\Error\LoaderError $e) {
            throw $e;
        } catch (Twig\Error\RuntimeError $e) {
            throw $e;
        } catch (Twig\Error\SyntaxError $e) {
            throw $e;
        }

        return $result;
    }

    public static function renderJson($content, $http_code = 200)
    {
        $header_string = 'Content-Type: text/javascript; charset=utf-8';
        header($header_string, true, $http_code);
        return json_encode($content, JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return \Twig\Environment
     */
    private static function _prepareTwigTemplate()
    {
        // loader
        $templatePath = rtrim(Config::getTemplateBase(), '/ ');
        $loader = new Twig\Loader\FilesystemLoader($templatePath);

        // options
        $options = [
            'cache' => rtrim(Config::getTemplateCacheBase(), '/'),
            'auto_reload' => true,
            'debug' => Config::getEnv('APP_DEBUG'),
            'trim_blocks' => true,
            'strict_variables' => false
        ];

        $twig = new Twig\Environment($loader, $options);
        $twig->addExtension(new Twig\Extensions\TextExtension());
        $twig->addExtension(new Filter());
        return $twig;
    }

}
