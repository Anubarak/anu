<?php

namespace anu\base;

/**
 * EntryTypeNotFoundException represents an exception caused by an invalid id.
 *
 * @author Robin Schambach
 */
class EntryTypeNotFoundException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'EntryType not found';
    }
}
