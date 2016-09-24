<?php
namespace Jasuwienas\MailBundle\Service;

use Jasuwienas\MailBundle\Component\Response;
use Jasuwienas\MailBundle\Entity\MailQueue;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\Translation\TranslatorInterface as Translator;
use Jasuwienas\MailBundle\Service\QueueManagerService as QueueManager;
use Jasuwienas\MailBundle\Service\Senders\Interfaces\MailSenderInterface as MailSender;
use Exception;

class ProcessQueueService {

    const MAX_EXECUTION_TIME = 55;

    /**
     * @var QueueManagerService
     */
    private $queueManager;

    /**
     * @var Container
     */
    private $container;

    /**
     * @var Translator
     */
    private $translator;

    /** @var int */
    private $startTime;

    /**
     * Currently processed email
     *
     * @var MailQueue
     */
    private $processedMail;

    public function __construct(QueueManager $queueManager, Container $container, Translator $translator) {
        $this->queueManager = $queueManager;
        $this->container = $container;
        $this->translator = $translator;
    }

    public function run() {
        $this->initialize();
        while($this->getExecutionTime() < self::MAX_EXECUTION_TIME) {
            $this->sendOneEmail();
        }
    }

    private function initialize() {
        $this->startTime = microtime(true);
    }

    /**
     * Sends one email from queue. Returns true for success and false for failure.
     *
     * @return bool
     */
    private function sendOneEmail() {
        $this->processedMail = $this->queueManager->pop();
        try {
            if (!$this->processedMail || !$this->processedMail instanceof MailQueue) {
                return $this->waitForEmails();
            }
            $serviceName = 'mail.sender.' . $this->processedMail->getAdapter();
            if (!$this->container->has($serviceName)) {
                return $this->handleNotExistingAdapter();
            }
            /** @var MailSender $mailSender */
            $mailSender = $this->container->get($serviceName);
            if(!$mailSender instanceof MailSender) {
                return $this->handleWrongAdapterInterface();
            }
            $response = $mailSender->send($this->processedMail);
            if(!$response instanceof Response) {
                return $this->handleWrongAdapterResponse();
            }
            if (!$response->getResult()) {
                throw new Exception($response->getError());
            }
            $this->queueManager->handleSuccess($this->processedMail);
        } catch(Exception $exception) {
            if($this->processedMail instanceof MailQueue) {
                $this->queueManager->handleNextAttempt($this->processedMail, $exception->getMessage());
            }
            return false;
        }
        return true;
    }

    /**
     * This method is executed when there are no emails ready to send.
     * It forces application to wait for new emails.
     *
     * @return bool
     */
    private function waitForEmails() {
        sleep(5);
        return false;
    }

    /**
     * Adapter does not exist. So we are not even waiting for next attempt, and setting error now.
     *
     * @return bool
     */
    private function handleNotExistingAdapter() {
        $errorMessage = $this->translator->trans('mailing.error.adapter');
        $this->queueManager->handleError($this->processedMail, $errorMessage);
        return false;
    }

    /**
     * Proper adapter does not exist. So we are not even waiting for next attempt, and setting error now.
     * Adapter exists but does not extend requested interface
     *
     * @return bool
     */
    private function handleWrongAdapterInterface() {
        $errorMessage = $this->translator->trans('mailing.error.adapter.interface');
        $this->queueManager->handleError($this->processedMail, $errorMessage);
        return false;
    }

    /**
     * Proper adapter does not exist. So we are not even waiting for next attempt, and setting error now.
     * Adapter exists but does not return proper response.
     *
     * @return bool
     */
    private function handleWrongAdapterResponse() {
        $errorMessage = $this->translator->trans('mailing.error.adapter.response');
        $this->queueManager->handleError($this->processedMail, $errorMessage);
        return false;
    }

    private function getExecutionTime() {
        return (microtime(true) - $this->startTime);
    }

}