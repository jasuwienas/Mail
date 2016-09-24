<?php
namespace Jasuwienas\MailBundle\Service\Senders;

use Jasuwienas\MailBundle\Component\Response;
use Jasuwienas\MailBundle\Entity\MailQueue;
use Jasuwienas\MailBundle\Service\Senders\Interfaces\MailSenderInterface;
use Exception;

class FreshMailSenderService implements MailSenderInterface {

    private $errors = [];
    private $strApiSecret   = null;
    private $strApiKey      = null;
    private $response    = null;
    private $rawResponse = null;
    private $httpCode    = null;
    private $contentType = 'application/json';
    private $freshMailHost = 'https://api.freshmail.com/';
    private $prefix = 'rest/';

    /**
     * @param $freshMailHost
     * @param $prefix
     * @param $apiKey
     * @param $secretKey
     */
    public function __construct($freshMailHost, $prefix, $apiKey, $secretKey) {
        $this->freshMailHost = $freshMailHost;
        $this->prefix = $prefix;
        $this->strApiSecret = $secretKey;
        $this->strApiKey = $apiKey;
    }

    /**
     * @param MailQueue $mailQueue
     * @return Response
     */
    public function send($mailQueue) {
        try {
            $data = [
                'subscriber' => $mailQueue->getRecipient(),
                'subject' => $mailQueue->getTitle(),
                'text' => $mailQueue->getPlainBody(),
                'html' => $mailQueue->getBody()
            ];
            $this->doRequest('mail', $data);
            return new Response(true);
        } catch(Exception $exception) {
            return new Response(false, $exception->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        if ( isset( $this->errors['errors'] ) ) {
            return $this->errors['errors'];
        }

        return false;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return array
     */
    public function getRawResponse()
    {
        return $this->rawResponse;
    }

    /**
     * @return array
     */
    public function getHttpCode()
    {
        return $this->httpCode;
    }

    /**
     * Sets secret api
     *
     * @param string $strSecret
     * @return FreshMailSenderService $this
     */
    public function setApiSecret($strSecret = '') {
        $this->strApiSecret = $strSecret;
        return $this;
    }

    /**
     * @param string $contentType
     * @return FreshMailSenderService $this
     */
    public function setContentType($contentType = '')  {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * Sets api key
     *
     * @param string $strKey
     * @return FreshMailSenderService $this
     */
    public function setApiKey ($strKey = '') {
        $this->strApiKey = $strKey;
        return $this;
    }

    public function doRequest($strUrl, $arrParams = array(), $boolRawResponse = false) {
        if ( empty($arrParams) ) {
            $strPostData = '';
        } elseif ( $this->contentType == 'application/json' ) {
            $strPostData = json_encode( $arrParams );
        } else {
            $strPostData = http_build_query( $arrParams );
        }
        $strSign = sha1( $this->strApiKey . '/' . $this->prefix . $strUrl . $strPostData . $this->strApiSecret );
        $arrHeaders = array();
        $arrHeaders[] = 'X-Rest-ApiKey: ' . $this->strApiKey;
        $arrHeaders[] = 'X-Rest-ApiSign: ' . $strSign;
        if ($this->contentType) {
            $arrHeaders[] = 'Content-Type: '.$this->contentType;
        }
        $resCurl = curl_init( $this->freshMailHost . $this->prefix . $strUrl );
        curl_setopt( $resCurl, CURLOPT_HTTPHEADER, $arrHeaders );
        curl_setopt( $resCurl, CURLOPT_HEADER, false );
        curl_setopt( $resCurl, CURLOPT_RETURNTRANSFER, true);

        if ($strPostData) {
            curl_setopt( $resCurl, CURLOPT_POST, true);
            curl_setopt( $resCurl, CURLOPT_POSTFIELDS, $strPostData );
        }

        $this->rawResponse = curl_exec( $resCurl );
        $this->httpCode = curl_getinfo( $resCurl, CURLINFO_HTTP_CODE );
        if ($boolRawResponse) {
            return $this->rawResponse;
        }
        $this->response = json_decode( $this->rawResponse, true );
        if ($this->httpCode != 200) {
            $this->errors = $this->response['errors'];
            if (is_array($this->errors)) {
                foreach ($this->errors as $arrError) {
                    throw new Exception($arrError['message'], $arrError['code']);
                }
            }
        }
        if (is_array($this->response) == false) {
            throw new Exception('Connection error - curl error message: '.curl_error($resCurl).' ('.curl_errno($resCurl).')');
        }

        return $this->response;
    }
}