<?php


namespace Commerce\App\Controller;


use Commerce\Core\Response;

class Error extends Base
{

    protected $_requireLogin = false;
    public $template_prefix = '';


    /**
     * @param string $message
     */
    public function api_authorize_error($message = "Authorization Required")
    {
        $status = new \stdClass();
        $status->status = 'NG';
        $status->message = $message;
        $this->output = Response::renderJson($status, 401);
    }

    /**
     * @param string $message
     */
    public function api_bad_request_error($message)
    {
        $status = new \stdClass();
        $status->status = 'NG';
        $status->message = $message;
        $this->output = Response::renderJson($status, 400);
    }

    /**
     * @param string $message
     */
    public function api_notfound_error($message)
    {
        $status = new \stdClass();
        $status->status = 'NG';
        $status->message = $message;
        $this->output = Response::renderJson($status, 404);
    }

    /**
     * @param string $message
     */
    public function api_server_error($message)
    {
        $status = new \stdClass();
        $status->status = 'NG';
        $status->message = $message;
        $this->output = Response::renderJson($status, 500);
    }
}
