<?php

namespace anu\base;

/**
 * ExitException represents a normal termination of an application.
 *
 * Do not catch ExitException. Anu will handle this exception to terminate the application gracefully.
 *
 * @author Robin Schambach
 */
class ExitException extends \Exception
{
    /**
     * @var int the exit status code
     */
    public $statusCode;


    /**
     * Constructor.
     * @param int $status the exit status code
     * @param string $message error message
     * @param int $code error code
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($status = 0, $message = null, $code = 0, \Exception $previous = null)
    {
        $this->statusCode = $status;
        parent::__construct($message, $code, $previous);
    }
}
