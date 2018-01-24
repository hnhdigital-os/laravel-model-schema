<?php

namespace HnhDigital\ModelAttributes\Exceptions;

class ValidationException extends \Exception
{
    /**
     * Validator.
     * 
     * @var Validator
     */
    protected $validator;

    /**
     * Exception constructor.
     *
     * @param string         $message
     * @param integer        $code
     * @param Exception|null $previous
     * @param Validator|null $validator
     */
    public function __construct($message = null, $code = 0, Exception $previous = null, $validator = null)
    {
        parent::__construct($message, $code, $previous);
        $this->validator = $validator;
    }

    /**
     * Get the validator.
     *
     * @return Validator
     */
    public function getValidator()
    {
        return $this->validator;
    }
}
