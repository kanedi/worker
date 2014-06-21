<?php
/**
 * Created by PhpStorm.
 * User: martinadiyono
 * Date: 3/19/14
 * Time: 4:15 PM
 */

use Phalcon\Mvc\User\Component,
    Phalcon\Mvc\View;

require_once __DIR__ . '/swiftmail/swift_required.php';

class Mail extends Component {
    protected $_transport;

    /**
     * Applies a template to be used in the e-mail
     *
     * @param string $name
     * @param array $params
     */
    public function getTemplate($name, $params)
    {
        global $config;

        $parameters = array_merge(array(
            'publicUrl' => $config->application->publicUrl,
        ), $params);

        return $this->view->getRender('emailTemplates', $name, $parameters, function($view){
            $view->setRenderLevel(View::LEVEL_LAYOUT);
        });

        return $view->getContent();
    }

    /**
     * Sends e-mails via gmail based on predefined templates
     *
     * @param array $to
     * @param string $subject
     * @param string $name
     * @param array $params
     */
    public function send($to, $subject, $name, $params, $att = null)
    {
        global $config;
        //Settings
        $mailSettings = $config->mail;

        $template = $this->getTemplate($name, $params);

        // Create the message
        $message = Swift_Message::newInstance()
            ->setSubject($subject)
            ->setTo($to)
            ->setFrom(array(
                $mailSettings->fromEmail => $mailSettings->fromName
            ))
            ->setBody($template, 'text/html');
        if ($att != null){
            if(is_array($att)){
                $message->attach(Swift_Attachment::fromPath($att['data'])->setFilename($att['filename']));
            }
        }
        if (!$this->_transport) {
            $this->_transport = Swift_SmtpTransport::newInstance(
                $mailSettings->smtp->server,
                $mailSettings->smtp->port,
                $mailSettings->smtp->security
            )
                ->setUsername($mailSettings->smtp->username)
                ->setPassword($mailSettings->smtp->password);
        }

        // Create the Mailer using your created Transport
        $mailer = Swift_Mailer::newInstance($this->_transport);

        return $mailer->send($message);
    }
} 