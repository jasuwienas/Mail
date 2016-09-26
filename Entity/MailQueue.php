<?php

namespace Jasuwienas\MailBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Jasuwienas\MailBundle\Service\QueueManagerService;

/**
 * MailQueue
 *
 * @ORM\Table("mail_queue")
 * @ORM\Entity(repositoryClass="Jasuwienas\MailBundle\Entity\MailQueueRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class MailQueue
{

    const STATUS_NEW = 0;
    const STATUS_PROCESSED = 1;
    const STATUS_SUCCESS = 2;
    const STATUS_TRY_AGAIN = 3;
    const STATUS_ERROR = 4;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="adapter", type="string", options={"fixed" = true}, length=32, nullable=true)
     */
    private $adapter;

    /**
     * @var string
     *
     * @ORM\Column(name="recipient", type="string", options={"fixed" = true}, length=255, nullable=true)
     */
    private $recipient;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", options={"fixed" = true}, length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text", nullable=true)
     */
    private $body;

    /**
     * @var string
     *
     * @ORM\Column(name="plain_body", type="text", nullable=true)
     */
    private $plainBody;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="smallint", length=1, options={"comment" = "0 - new, 1 - processed, 2 - success, 3 - try again, 4 - error"}, nullable=false)
     */
    private $status = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="error", type="string", options={"fixed" = true}, length=255, nullable=true)
     */
    private $error;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="send_at", type="datetime", nullable=true)
     */
    private $sendAt;

    /**
     * @var int
     *
     * @ORM\Column(name="attempts", type="smallint", length=1, options={"comment" = "counts number of sending attempts"}, nullable=false)
     */
    private $attempts = 0;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set adapter
     *
     * @param string $adapter
     *
     * @return MailQueue
     */
    public function setAdapter($adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Get adapter
     *
     * @return string
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set recipient
     *
     * @param string $recipient
     *
     * @return MailQueue
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get recipient
     *
     * @return string
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return MailQueue
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param string $body
     *
     * @return MailQueue
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }


    /**
     * Set plain body
     *
     * @param string $plainBody
     *
     * @return MailQueue
     */
    public function setPlainBody($plainBody)
    {
        $this->plainBody = $plainBody;
        return $this;
    }

    /**
     * Get plain body
     *
     * @return string
     */
    public function getPlainBody()
    {
        return $this->plainBody;
    }

    /**
     * Set status
     *
     * @param int $status
     * @return MailQueue
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set error
     *
     * @param string $error
     *
     * @return MailQueue
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set created at
     *
     * @param DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get created at
     *
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updated at
     *
     * @param DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updated at
     *
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set send at
     *
     * @param DateTime $sendAt
     * @return $this
     */
    public function setSendAt($sendAt)
    {
        $this->sendAt = $sendAt;

        return $this;
    }

    /**
     * Get send at
     *
     * @return DateTime
     */
    public function getSendAt()
    {
        return $this->sendAt;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $now = new Datetime;
        if(!$this->getCreatedAt()) {
            $this->setCreatedAt($now);
        }
        if(!$this->getSendAt()) {
            $this->setSendAt($now);
        }
        $this->setUpdatedAt($now);
    }

    /**
     * Set attempts
     *
     * @param int $attempts
     * @return MailQueue
     */
    public function setAttempts($attempts)
    {
        $this->attempts = $attempts;
        return $this;
    }

    /**
     * Get attempts
     *
     * @return int
     */
    public function getAttempts()
    {
        return $this->attempts;
    }

    /**
     * @return int
     */
    public function getAttemptsLeft() {
        return QueueManagerService::MAX_SENDING_ATTEMPTS - $this->attempts;
    }


    public function requestNextSendingAttempt() {
        $this->attempts++;
        $this->sendAt = new DateTime('+5 minutes');
        $this->status = self::STATUS_TRY_AGAIN;
    }
}
