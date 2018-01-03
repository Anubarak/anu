<?php

namespace anu\base;

/**
 * InvalidConfigException represents an exception caused by incorrect object configuration.
 *
 * @author Robin Schambach
 */
class InvalidConfigException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Invalid Configuration';
    }
}
