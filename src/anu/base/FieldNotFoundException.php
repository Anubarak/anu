<?php

namespace anu\base;

/**
 * FieldNotFoundException is thrown when a field could not be found with the given attributes
 *
 * @author Robin Schambach
 */
class FieldNotFoundException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Field not found';
    }
}
