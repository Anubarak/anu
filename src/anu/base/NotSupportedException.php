<?php
namespace anu\base;

/**
 * NotSupportedException represents an exception caused by accessing features that are not supported.
 *
 * @author Robin Schambach
 */
class NotSupportedException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Not Supported';
    }
}
