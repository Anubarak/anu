<?php

namespace anu\base;

/**
 * InvalidCallException represents an exception caused by calling a method in a wrong way.
 *
 * @author Robin Schambach
 */
class InvalidCallException extends \BadMethodCallException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Call';
    }
}
