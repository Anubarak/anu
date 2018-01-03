<?php

namespace anu\db;

/**
 * Exception represents an exception that is caused by violation of DB constraints.
 *
 * @author Robin Schambach
 */
class QueryAbortedException extends Exception
{
    /**
     * @return string the user-friendly name of this exception
     */
    public function getName()
    {
        return 'Query Aborted Exception';
    }
}
