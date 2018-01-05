<?php

namespace anu\base;

/**
 * ElementNotFoundException represents an exception caused by an invalid id.
 *
 * @author Robin Schambach
 */
class ElementNotFoundException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Element not found';
    }
}
