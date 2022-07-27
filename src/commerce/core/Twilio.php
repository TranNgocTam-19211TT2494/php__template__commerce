<?php
namespace Commerce\Core;

use Twilio\Exceptions\ConfigurationException;
use Twilio\Rest\Client;

class Twilio
{
    public static function sms($number, $message)
    {
        $sid = Config::getEnv('TWILIO_ACCOUNT_SID');
        $token = Config::getEnv('TWILIO_TOKEN');

        try {
            $client = new Client($sid, $token);
            $client->messages->create(
            // the number you'd like to send the message to
                '+81' . ltrim($number,'0'),
                [
                    'from' => Config::getEnv('TWILIO_SMS_PHONE_NUMBER'),
                    'body' => $message
                ]
            );
        } catch (ConfigurationException $e) {
            Logger::log('info', $e->getMessage(), 'twilio');
            return false;
        }
        return true;
    }

    public static function call($number)
    {
        $sid = Config::getEnv('TWILIO_ACCOUNT_SID');
        $token = Config::getEnv('TWILIO_TOKEN');

        try {
            $client = new Client($sid, $token);
            $client->calls->create(
                '+81' . ltrim($number,'0'),
                Config::getEnv('TWILIO_CALL_PHONE_NUMBER'),
                [
                    "twiml" => "<Response><Say language=\"ja-jp\">ピーカチテイクアウトです。新しいオーダーがあります。管理画面からご確認いただき対応をお願いいたします。</Say></Response>"
                ]
            );
        } catch (ConfigurationException $e) {
            Logger::log('info', $e->getMessage(), 'twilio');
            return false;
        }
        return true;
    }
}