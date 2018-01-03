<?php
namespace anu\events;

use anu\base\Event;

/**
 * CancelableEvent class.
 *
 * @author Robin Schambach
 */
class CancelableEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var bool Whether to continue performing the action that called this event
     */
    public $isValid = true;
}
