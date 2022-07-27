<?php
namespace Commerce\Core;

use Aws\AwsClient;
use Aws\Exception\AwsException;
use Aws\Ses\SesClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use SendGrid\Mail\Mail;
use Meguru\App\Utils\Format;
use Meguru\Core\Email\Grammar;

class Email
{
    /**
     * From email name
     * @var string
     */
    private $fromName;

    /**
     * From email address
     * @var string
     */
    private $fromAddress;

    /**
     * to mail
     * @var string|array
     */
    private $toAddress = [];

    /**
     * Bcc address
     * @var array
     */
    private $bcc = [];

    /**
     * Reply address
     * @var array
     */
    private $replyToAddress = [];

    /**
     * Email title
     * @var string
     */
    private $subject;

    /**
     * the content of the email
     * @var string
     */
    private $body;


    /**
     * the content of the email
     * @var string
     */
    private $body_html;

    /**
     * Attachment
     * @var array
     */
    private $attaches = [];

    /**
     * instance
     * @var Object Self instance
     */
    static $instance;

    /**
     * Get an instance
     * @return Email Object
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Email;

            $provider = Config::getEnv('EMAIL_PROVIDER');
            if ($provider == 'Local') {
                \Swift::init(
                    function () {
                        \Swift_DependencyContainer::getInstance()
                            ->register('email.validator')
                            ->asSharedInstanceOf(Grammar::class);
                    }
                );
            }
        }

        $instance = self::$instance;
        $instance->clearAll();

        return $instance;
    }

    /**
     * send an email
     * @return bool
     */
    public function send()
    {
        Logger::log('debug', $this->body, 'email');
        $provider = Config::getEnv('EMAIL_PROVIDER');
        $result = true;
        switch ($provider) {
            case 'AWS':
                $result = $this->_sendByAwsSES();
                break;
            case 'Smtps':
                $result = $this->_sendBySmtps();
                break;
            case 'Local':
                $result = $this->_sendByLocal();
                break;
            case 'SendGrid':
                $result = $this->_sendBySendGrid();
                break;
        }
        return $result;
    }

    /**
     * send an email. aws SES
     * @return bool
     */
    public function _sendByAwsSES()
    {
        $subject = $this->subject;
        if (Config::getEnv('APP_MODE') != "production") {
            $subject = "[TEST] " . $this->subject;
        }

        $to_address = is_array($this->toAddress) ? $this->toAddress : explode(",", $this->toAddress);
        Logger::log('info', $subject . PHP_EOL . implode(",", $to_address), "mail");

        $data = [];
        $data['Destination']['ToAddresses'] = $to_address;
        if ($this->bcc) {
            $data['Destination']['BccAddresses'] = $this->bcc;
        }
        if ($this->replyToAddress) {
            $data['ReplyToAddresses'] = $this->replyToAddress;
        }

        $data['Source'] = $this->fromName ? mb_encode_mimeheader($this->fromName, "UTF-8") . " <{$this->fromAddress}>" : $this->fromAddress;
        $data['Message'] = [
            'Body' => [
                'Text' => [
                    'Charset' => 'UTF-8',
                    'Data' => $this->body,
                ],
            ],
            'Subject' => [
                'Charset' => 'UTF-8',
                'Data' => $subject,
            ],
        ];
        if ($this->body_html) {
            $data['Message']['Body']['Html'] = [
                'Charset' => 'UTF-8',
                'Data' => $this->body_html,
            ];
        }

        $client = new SesClient([
            'region' => 'us-west-2',
            'version' => 'latest',
            'credentials' => [
                'key' => Config::getEnv("AWS_ACCESS_KEY"),
                'secret' => Config::getEnv("AWS_SERCRET_KEY")]
        ]);

        Logger::log('debug', json_encode($data, JSON_UNESCAPED_UNICODE), "mail");
        $result = true;
        try {
            $client->sendEmail($data);
        } catch (AwsException $e) {
            Logger::log('info', $subject . PHP_EOL . $e->getMessage() . PHP_EOL . $e->getAwsErrorMessage(), "mail");
            $result = false;
        }

        return $result;
    }

    /**
     * send e-mail HENGE SMTPS
     * @return bool
     */
    private function _sendBySmtps()
    {
        $subject = $this->subject;
        if (Config::getEnv('APP_MODE') != "production") {
            $subject = "[TEST] " . $this->subject;
        }
        $to_address = is_array($this->toAddress) ? $this->toAddress : explode(",", $this->toAddress);

        $to = [];
        foreach ($to_address as $address) {
            $row = new \stdClass();
            $row->name = "";
            $row->address = $address;
            $to[] = $row;
        }
        Logger::log('info', $subject . PHP_EOL . implode(",", $to_address), "mail");
        $data = [
            "api_user" => Config::getEnv("SMTPS_API_USER"),
            "api_key" => Config::getEnv("SMTPS_API_KEY"),
            "from" => [
                'name' => $this->fromName,
                'address' => $this->fromAddress
            ],
            "to" => $to,
            "subject" => $subject,
            "text" => $this->body,
        ];
        if ($this->body_html) {
            $data["html"] = $this->body_html;
        }

        if ($this->replyToAddress) {
            foreach ($this->replyToAddress as $replyToAddress) {
                $data['replyto'] = [
                    "name" => "",
                    "address" => $replyToAddress
                ];
            }
        }

        Logger::log('debug', json_encode($data, JSON_UNESCAPED_UNICODE), "mail");
        $result = true;
        try {
            $client = new Client();
            $client->post(Config::getEnv("SMTPS_API_URI"), [
                'debug' => false,
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $data
            ]);
        } catch (ClientException $e) {
            $result = false;
            Logger::log('info', $subject . PHP_EOL . $e->getMessage(), "mail");
        }
        return $result;
    }

    /**
     * Send email from local
     * @return bool
     */
    private function _sendByLocal()
    {
        $sendmail_path = ini_get('sendmail_path');
        $transport = new \Swift_SendmailTransport($sendmail_path);
        $mailer = new \Swift_Mailer($transport);

        $subject = $this->subject;
        if (Config::getEnv('APP_MODE') != "production") {
            $subject = "[TEST] " . $this->subject;
        }

        $to_address = is_array($this->toAddress) ? implode(",", $this->toAddress) : $this->toAddress;
        Logger::log('info', $subject . PHP_EOL . $to_address, "mail");

        $result = true;
        try {
            $message = (new \Swift_Message($subject))
                ->setFrom([$this->fromAddress => $this->fromName])
                ->setTo($this->toAddress)
                ->setBody($this->body, 'text/plain');
            if ($this->body_html) {
                $message->addPart($this->body_html, 'text/html');
            }
            if ($this->replyToAddress) {
                $message->setReplyTo($this->replyToAddress);
            }
            if ($this->bcc) {
                $message->setBcc($this->bcc);
            }
            $mailer->send($message);
        } catch (\Swift_TransportException $ex) {
            Logger::log('info', "TRANS " . $ex->getMessage(), "mail");
            $result = false;
        } catch (\Swift_RfcComplianceException $ex) {
            Logger::log('info', "RFC " . $ex->getMessage(), "mail");
            $result = false;
        } catch (\Exception $ex) {
            $result = false;
            Logger::log('info', $ex->getMessage(), "mail");
        }
        if ($result) {
            Logger::log('info', $subject . PHP_EOL . $this->body, "mail_body");
        }

        return $result;
    }

    public function _sendBySendGrid()
    {
        $subject = $this->subject;
        if (Config::getEnv('APP_MODE') != "production") {
            $subject = "[TEST] " . $this->subject;
        }

        $email = new Mail();
        $email->setFrom($this->fromAddress, $this->fromName);
        $email->setSubject($subject);
        $email->addTo($this->toAddress[0]);
        $email->addContent("text/plain", $this->body);
        if ($this->body_html) {
            $email->addContent("text/html", $this->body_html);
        }
        if ($this->replyToAddress) {
            $email->setReplyTo($this->replyToAddress[0]);
        }
        if ($this->bcc) {
            $email->addBcc($this->bcc[0]);
        }

        $sendgrid = new \SendGrid(Config::getEnv('SENDGRID_API_KEY'));
        $result = true;
        try {
            $sendgrid->send($email);
        } catch (\Exception $e) {
            $result = false;
            Logger::log('info', $subject . PHP_EOL . $e->getMessage(), "mail");
        }
        return $result;
    }

    /**
     * Set email name
     * @param $value
     * @return void
     */
    public function setFromName($value)
    {
        $this->fromName = $value;
    }

    /**
     * Set From email address
     * @param $value
     * @return void
     */
    public function setFromAddress($value)
    {
        $this->fromAddress = $value;
    }

    /**
     * set to email
     * @param $value
     * @return void
     */
    public function setToAddress($value)
    {
        $this->toAddress[] = $value;
    }

    /**
     * set reply to email
     * @param $value
     */
    public function setReplyTo($value)
    {
        $this->replyToAddress[] = $value;
    }

    /**
     * bcc to email
     * @param $value
     * @return void
     */
    public function setBcc($value)
    {
        $this->bcc[] = $value;
    }

    /**
     * set title email
     * @param $value
     * @return void
     */
    public function setSubject($value)
    {
        $this->subject = $value;
    }

    /**
     * set body email
     * @param $value
     * @return void
     */
    public function setBody($value)
    {
        $this->body = Format::replaceReturn($value);
    }

    /**
     * set email (HTML)
     * @param $value
     * @return void
     */
    public function setHtml($value)
    {
        $this->body_html = $value;
    }

    /**
     * set attachment
     * @param $value
     */
    public function setAttaches($value)
    {
        $this->attaches[] = $value;
    }

    /**
     * Initialize user-specific data: for loops
     * @return void
     */
    public function clearRcpt()
    {
        $this->toAddress = [];
        $this->replyToAddress = [];
        $this->subject = null;
        $this->body = null;
        $this->attaches = [];
    }

    /**
     * clear to address and reply to address
     * @return void
     */
    public function clearTo()
    {
        $this->toAddress = [];
        $this->replyToAddress = [];
    }

    /**
     * clear all
     * @return void
     */
    public function clearAll()
    {
        $this->toAddress = [];
        $this->fromName = null;
        $this->fromAddress = null;
        $this->bcc = [];
        $this->replyToAddress = [];
        $this->subject = null;
        $this->body = null;
        $this->body_html = null;
        $this->attaches = [];
    }
}