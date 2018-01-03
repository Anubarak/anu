<?php

namespace anu\base;

/**
 * InvalidRouteException represents an exception caused by an invalid route.
 *
 * @author Robin Schambach
 */
class InvalidRouteException extends UserException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Route';
    }
}
