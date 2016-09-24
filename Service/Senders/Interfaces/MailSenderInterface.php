<?php
namespace Jasuwienas\MailBundle\Service\Senders\Interfaces;

use Jasuwienas\MailBundle\Entity\MailQueue;
use Jasuwienas\MailBundle\Component\Response;

interface MailSenderInterface {

    /**
     * @param MailQueue mailQueue
     * @return Response
     */
    public function send($mailQueue);


}