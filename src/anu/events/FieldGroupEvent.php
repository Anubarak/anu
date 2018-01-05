<?php

namespace anu\events;

use anu\models\FieldGroup;
use anu\base\Event;

/**
 * FieldGroupEvent class.
 *
 * @author Robin Schambach
 */
class FieldGroupEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var FieldGroup|null The field group associated with this event.
     */
    public $group;
    /**
     * @var bool Whether the field group is brand new
     */
    public $isNew = false;
}
