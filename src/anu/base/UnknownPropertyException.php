<?php

namespace anu\base;

/**
 * UnknownPropertyException represents an exception caused by accessing unknown object properties.
 *
 * @author Robin Schambach
 */
class UnknownPropertyException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Unknown Property';
    }
}
