<?php

namespace Folha\Log\app\Services\Log;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Monolog\Handler\NativeMailerHandler;
use Monolog\Handler\SwiftMailerHandler;
use Swift_Message;
use Swift_Mailer;
use Swift_SmtpTransport;
use Monolog\Logger;
use DateTime;
use Throwable;
use Illuminate\Support\Facades\Log;

class EmailErrorLogger
{
    const FNORD_SUBJECT = 'ERROR - %s (%s) %s @';
    /**
     * Configurações do e-mail
     */
    private $mail_host;
    private $mail_port;
    private $mail_encryption;
    private $mail_username;
    private $mail_password;
    private $mail_from;
    private $mail_from_name;
    private $mail_to;
    private $mail_subject;
    public static $mail_subject_custom = null;

    /**
     * Dados gerais sobre o log
     */
    private $log_time;

    /**
     * Recebe o driver de e-mail configurado
     */
    private $mail_driver;

    /**
     * Instância do Logger
     * @var $logger Logger
     */
    private $logger;

    public function __construct()
    {
        $this->log_time = (new DateTime())->format('d/m/Y H:i:s O');

        // Configurações de Conexão do E-mail
        $this->mail_driver = config('mail.default', 'log');
        $this->mail_host = config('mail.host', 'smtp.titan.email');
        $this->mail_port = config('mail.port', 465);
        $this->mail_encryption = config('mail.encryption', null);
        $this->mail_username = config('mail.username', null);
        $this->mail_password = config('mail.password', null);

        // Configuração da mensagem do E-mail
        $this->mail_from = config('mail.from.address', 'developer@fabiovige.com.br');
        $this->mail_from_name = config('mail.from.name', 'Developer');
        $this->mail_to = config('mail.to', 'developer@fabiovige.com.br');
        $this->mail_subject = self::$mail_subject_custom ?? sprintf(self::FNORD_SUBJECT, ucfirst(strtolower(env('APP_NAME'))), App::environment(), '');

        // Instância do Logger
        $this->logger = new Logger('maillog');
    }


    /**
     *  Esta classe vai criar uma instância Monolog personalizada
     *
     * @param array $config
     * @return Logger
     * @throws Exception
     */
    public function __invoke(array $config)
    {
        $this->configLogger();
        return $this->getLogger();
    }

    public function setLogger($logger)
    {
        $this->logger = $logger;
    }

    public function getLogger()
    {
        if (config('logging.channels.maillog.mail') == false) {
            return new Logger('maillog');
        }

        return $this->logger;
    }

    public function configLogger()
    {
        // Configuração do Handler de monolog
        $this->configHandler();

        // Configuração personalizada do formatter
        $this->configFormatter();

        // Insere informações adicionais no registro de log
        $this->extraLogInfo();
    }

    /**
     * <b>configHandler</b>: Configura o Handler do monolog para o disparo de e-mail
     * Caso esteja sendo disparado em ambiente local, enviar pelo smtp da folha
     * Caso contrário, enviar nativamente pelos relays do sistema
     * Caso não tenha nenhum MAIL_DRIVER configurado, será considerado e armazenado como log
     *
     */
    private function configHandler()
    {
        if ($this->mail_driver === 'smtp') {
            $this->configSmtpHandler();
            return;
        }

        $this->configNativeHandler();
    }

    /**
     * <b>configSmtpHandler</b>: Configura o handler para disparo do log via SMTP
     */
    private function configSmtpHandler()
    {
        // Criando Swift Transport
        $transporter = new Swift_SmtpTransport($this->mail_host, $this->mail_port, $this->mail_encryption);
        $transporter->setUsername($this->mail_username);
        $transporter->setPassword($this->mail_password);

        // Criando o Mailer usando o transport criado
        $mailer = new Swift_Mailer($transporter);

        // Criando a mensagem
        $message = (new Swift_Message(($this->mail_subject)));
        $message->setFrom($this->mail_from, $this->mail_from_name);
        $message->setTo($this->mail_to, $this->mail_from_name);

        $this->logger->pushHandler(new SwiftMailerHandler($mailer, $message, Logger::ERROR));
    }

    /**
     * <b>configNativeHandler</b>: Configura o handler para disparo do log nativamente (mail())
     */
    private function configNativeHandler()
    {
        $this->logger->pushHandler(
            new NativeMailerHandler(
                $this->mail_to,
                $this->mail_subject,
                $this->mail_from_name . $this->mail_from,
                Logger::ERROR
            )
        );
    }

    /**
     * <b>extraLogInfo</b>: configurações para informações adicionais do log
     */
    private function extraLogInfo()
    {
        // Busca pelo IP do usuário
        // Esta verificação é necessária pois o ambiente em prod
        // possui VIPs para realizar o balanceamento de carga
        $client_ip = null;

        if (Arr::has($_SERVER, 'HTTP_X_FORWARDED_FOR')) {
            $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $client_ip = $ip_list[0];
        }

        if (Arr::has($_SERVER, 'REMOTE_ADDR')) {
            $client_ip = $_SERVER['REMOTE_ADDR'];
        }

        // Informações extras do log
        $this->logger->pushProcessor(function ($record) use ($client_ip) {
            $record['service'] = env('APP_NAME') . ' (' . App::environment() . ')';
            $record['notification'] = 'ERROR';
            $record['extra'] = $_SERVER;
            $record['extra']['IP'] = $client_ip;
            return $record;
        });
    }

    /**
     * <b>configFormatter</b>: configura o Formatter dos handlers monolog
     */
    private function configFormatter()
    {
        foreach ($this->logger->getHandlers() as $handler) {
            $handler->setFormatter(new EmailErrorFormatter($this->log_time));
        }
    }

    public function sendLogError(Throwable $exception)
    {
        $backtrace_message = $exception->getTraceAsString();
        list(, $backtrace_message) = explode("#3 ", $backtrace_message, 2);
        $backtrace_message = "\n#3 " . $backtrace_message;

        self::$mail_subject_custom = sprintf(self::FNORD_SUBJECT, ucfirst(strtolower(env('APP_NAME'))), App::environment(), $exception->getMessage());

        $context = [
            'Code' => $exception->getCode(),
            'File' => $exception->getFile(),
            'Line' => $exception->getLine(),
            'Backtrace Info' => $backtrace_message,
            'Request' => json_encode(request()->all()),
        ];

        if (get_class($exception) === \Facade\Ignition\Exceptions\ViewException::class && isset($exception->context()['view'], $exception->context()['view']['view'])) {
            $context['View'] = $exception->context()['view']['view'];
        }

        // Disparar log de erro por e-mail
        Log::channel('maillog')->error(
            $exception->getMessage(),
            $context
        );
    }
}
