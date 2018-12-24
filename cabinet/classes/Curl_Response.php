<?php
namespace Cabinet;


/**
 * Class Curl_Response
 * @package Cabinet
 */
class Curl_Response {

    private $headers       = [];
    private $http_code     = null;
    private $content_type  = '';
    private $info          = [];
    private $content       = '';
    private $is_error      = false;
    private $error_message = '';
    private $error_code    = 0;


    /**
     * Curl_Response constructor.
     * @param resource $curl
     * @throws \Exception
     */
    public function __construct($curl) {

        if (is_resource($curl)) {
            $this->content  = curl_exec($curl);
            $this->info     = curl_getinfo($curl);

            if ( ! empty($this->info['http_code'])) {
                $this->http_code = (int)$this->info['http_code'];
            }
            if ( ! empty($this->info['content_type'])) {
                $this->content_type = $this->info['content_type'];
            }

            if (curl_errno($curl) > 0) {
                $this->is_error      = true;
                $this->error_message = curl_error($curl);
                $this->error_code    = curl_errno($curl);
            }
        } else {
            throw new \Exception('Incorrect parameter curl. Need resource descriptor');
        }
    }


    /**
     * @return string
     */
    public function __toString() {
        return $this->content;
    }


    /**
     * @return string
     */
    public function getContent() {
       return $this->content;
    }


    /**
     * @return int
     */
    public function getHttpCode() {
       return $this->http_code;
    }


    /**
     * @return string
     */
    public function getContentType() {
       return $this->content_type;
    }


    /**
     * @return array
     */
    public function getInfo() {
       return $this->info;
    }


    /**
     * @return bool
     */
    public function isError() {
       return $this->is_error;
    }


    /**
     * @return array
     */
    public function getHeaders() {
       return $this->headers;
    }


    /**
     * @return int
     */
    public function getErrorCode() {
       return $this->error_code;
    }


    /**
     * @return string
     */
    public function getErrorMessage() {
       return $this->error_message;
    }


    /**
     * @return array|null
     */
    public function toArray() {

        $content = json_decode($this->content, true);

        if (json_last_error()) {
            return null;
        }

        return $content;
    }
}