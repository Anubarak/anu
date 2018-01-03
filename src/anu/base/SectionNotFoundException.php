<?php

namespace anu\base;

/**
 * SectionNotFoundException represents an exception caused by an invalid id.
 *
 * @author Robin Schambach
 */
class SectionNotFoundException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Section not found';
    }
}
