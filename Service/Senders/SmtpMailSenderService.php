<?php
namespace Jasuwienas\MailBundle\Service\Senders;

use Jasuwienas\MailBundle\Component\Response;
use Jasuwienas\MailBundle\Entity\MailQueue;
use Jasuwienas\MailBundle\Service\Senders\Interfaces\MailSenderInterface;
use Swift_Mailer as Mailer;
use Swift_Message as Message;
use Exception;

class SmtpMailSenderService implements MailSenderInterface {


    /**
     * @param Mailer $mailer
     * @param string $from
     */
    public function __construct(Mailer $mailer, $from) {
        $this->mailer = $mailer;
        $this->from = $from;
    }

    /**
     * @param MailQueue $mailQueue
     * @return Response
     */
    public function send($mailQueue)
    {
        try {
            $message = Message::newInstance()
                ->setSubject($mailQueue->getTitle())
                ->setFrom($this->from)
                ->setTo($mailQueue->getRecipient())
                ->setBody($mailQueue->getBody(), 'text/html')
                ->addPart($mailQueue->getPlainBody(), 'text/plain')
            ;
            $this->mailer->send($message);
            return new Response(true);
        } catch(Exception $exception) {
            return new Response(false, $exception->getMessage());
        }
    }
}