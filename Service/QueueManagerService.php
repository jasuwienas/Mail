<?php
namespace Jasuwienas\MailBundle\Service;

use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Jasuwienas\MailBundle\Entity\MailQueue;
use DateTime;

class QueueManagerService {

    const MAX_SENDING_ATTEMPTS = 5;

    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $recipient - email
     * @param string $title
     * @param string $body
     * @param DateTime|null $sendAt
     * @param string $adapter
     */
    public function push($recipient, $title, $body, $sendAt = null, $adapter = 'freshmail') {
        $queueElement = $this->create($recipient, $title, $body, $sendAt, $adapter);
        $this->save($queueElement);
    }

    /**
     * @param string $recipient - email
     * @param string $title
     * @param string $body
     * @param DateTime|null $sendAt
     * @param string $adapter
     * @return MailQueue
     */
    public function create($recipient, $title, $body, $sendAt = null, $adapter = 'freshmail') {
        if(!$sendAt) {
            $sendAt = new DateTime();
        }
        $queueElement = new MailQueue();
        $queueElement
            ->setAdapter(strtolower($adapter))
            ->setRecipient($recipient)
            ->setTitle($title)
            ->setBody($body)
            ->setPlainBody(strip_tags($body))
            ->setSendAt($sendAt)
        ;
        return $queueElement;
    }

    /**
     * @return MailQueue|null
     */
    public function pop() {
        if($queueElement = $this->entityManager->getRepository('MailBundle:MailQueue')->getFirstReadyToProcess()) {
            $queueElement->setStatus(MailQueue::STATUS_PROCESSED);
            $this->save($queueElement);
        }
        return $queueElement;
    }

    /**
     * @param MailQueue $queueElement
     * @param string $errorMessage
     */
    public function handleError($queueElement, $errorMessage) {
        $queueElement
            ->setStatus(MailQueue::STATUS_ERROR)
            ->setError($errorMessage)
        ;
        $this->save($queueElement);
    }

    /**
     * @param MailQueue $queueElement
     */
    public function handleSuccess($queueElement) {
        $queueElement->setStatus(MailQueue::STATUS_SUCCESS);
        $this->save($queueElement);
    }

    /**
     * @param MailQueue $queueElement
     * @param string $attemptReason
     */
    public function handleNextAttempt($queueElement, $attemptReason = '') {
        $queueElement->setError($attemptReason)->requestNextSendingAttempt();
        if($queueElement->getAttempts() >= self::MAX_SENDING_ATTEMPTS) {
            $this->handleError($queueElement, $attemptReason);
            return;
        }
        $this->save($queueElement);
    }

    /**
     * @param MailQueue $queueElement
     */
    public function save(&$queueElement) {
        $this->entityManager->persist($queueElement);
        $this->entityManager->flush();
    }

}