<?php
namespace Jasuwienas\MailBundle\Component;

class Response
{

    /** @var bool */
    private $result;

    /** @var string */
    private $errorMessage;

    /**
     * @param bool $result
     * @param null $error
     */
    public function __construct($result = null, $error = null) {
        $this->result = $result;
        $this->errorMessage = $error;
    }

    /**
     * @param bool $result
     * @return Response $this
     */
    public function setResult($result) {
        $this->result = $result;
        return $this;
    }

    /**
     * @return bool
     */
    public function getResult() {
        return $this->result;
    }

    /**
     * @param string $error
     * @return Response $this
     */
    public function setError($error) {
        $this->errorMessage = $error;
        return $this;
    }

    /**
     * @return string
     */
    public function getError() {
        return $this->errorMessage;
    }

}