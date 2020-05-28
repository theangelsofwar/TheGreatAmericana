<?php
namespace AmazonImages\Services\PAApi\Exceptions;

/**
 * Class ApiException
 * @package AmazonImages\Services\PAApi\Exceptions
 */
class ApiException extends \Exception{
    protected $errors=[];
    // Redefine the exception so message isn't optional

    /**
     * AmazonApiException constructor.
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message, $code = 0,\Exception $previous = null) {
        // make sure everything is assigned properly
        parent::__construct($message, $code, $previous);
    }

    // custom string representation of object
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    /**
     * @return array
     */
	public function getErrors() {
		return $this->errors;
	}

    /**
     * @param $ASIN
     * @param string $Message
     * @param string $Code
     */
	public function addError($ASIN,$Message,$Code){
		$this->errors[]=['ASIN'=>$ASIN,'Message'=>$Message,'Code'=>$Code];
	}
}
