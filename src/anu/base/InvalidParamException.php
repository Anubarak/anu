<?php

namespace anu\base;

/**
 * InvalidParamException represents an exception caused by invalid parameters passed to a method.
 *
 * @author Robin Schambach
 */
class InvalidParamException extends \BadMethodCallException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Parameter';
    }
}
