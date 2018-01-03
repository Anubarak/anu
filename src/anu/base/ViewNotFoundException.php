<?php

namespace anu\base;

/**
 * ViewNotFoundException represents an exception caused by view file not found.
 *
 * @author Robin Schambach
 */
class ViewNotFoundException extends InvalidParamException
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'View not Found';
    }
}
