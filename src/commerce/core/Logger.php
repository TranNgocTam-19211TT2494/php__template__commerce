<?php
namespace Commerce\Core;

use Carbon\Carbon;

class Logger
{
    /**
     * Log
     * @param $level
     * @param $data
     * @param $filename
     * @return void
     */
    public static function log($level, $data, $filename = "application")
    {
        $is_production = Config::getEnv('APP_MODE') == 'production';
        $is_debug = Config::getEnv('APP_DEBUG');
        if ($level == "debug" && !$is_debug) {
            return;
        }
        if ($level == "fatal" && $is_production) {
            $email = Email::getInstance();
            $email->setToAddress(Config::getEnv('ERROR_EMAIL_ADDRESS'));
            $email->setFromAddress(Config::getEnv('EMAIL_FROM'));
            $email->setFromName(Config::getEnv('SYSTEM_NAME'));
            $email->setSubject("[" . Config::getEnv('APP_NAME') . "] System Error Has Occur");
            $email->setBody($data);
            $email->send();
        }

        $now = Carbon::now();
        if($filename == 'gpayclient') {
            $logFile = Config::getLogBase() . $filename . ".log";
        }else if($filename == 'cron'){
                $logFile = Config::getLogBase() . $filename . "." . $now->format('Y-m') . ".log";
        }else{
            $filename = ($is_production) ? 'app' : $filename;
            $logFile = Config::getLogBase() . $filename . "." . $now->format('Y-m') . ".log";
        }
        $line = $now->format('Y-m-d H:i:s') . " [" . $level . "] ------------------------\n" . $data . "\n";
        File::write($logFile, $line, FILE_APPEND);
    }
}