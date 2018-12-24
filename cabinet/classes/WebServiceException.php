<?php
namespace Cabinet;

/**
 * Class WebServiceException
 * @package Cabinet
 */
class WebServiceException extends \Exception {

    protected $http_code;
    protected $code;


    /**
     * WebServiceException constructor.
     * @param string          $message
     * @param string          $code
     * @param int|string|null $http_code
     */
    public function __construct($message, $code = null, $http_code = null) {
        parent::__construct($message, $http_code);
        $this->code      = $code;
        $this->http_code = $http_code;
    }


    /**
     * @return int|string|null
     */
    public function getHttpCode() {
        return $this->http_code;
    }
}