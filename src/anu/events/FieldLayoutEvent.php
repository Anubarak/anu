<?php

namespace anu\events;

use anu\models\FieldLayout;
use anu\base\Event;

/**
 * Field layout Event class.
 *
 * @author Robin Schambach
 */
class FieldLayoutEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var FieldLayout|null The field layout associated with this event.
     */
    public $layout;
    /**
     * @var bool Whether the field is brand new
     */
    public $isNew = false;
}
