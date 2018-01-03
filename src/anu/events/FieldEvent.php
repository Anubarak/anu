<?php

namespace anu\events;

use anu\base\Event;
use anu\models\Field;

/**
 * ElementEvent represents the parameter needed by [[Element]] events.
 *
 * @author Robin Schambach
 */
class FieldEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var Field|null The field associated with this event.
     */
    public $field;

    /**
     * @var bool Whether the field is brand new
     */
    public $isNew = false;
}
