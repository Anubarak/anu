<?php

namespace anu\events;

use anu\base\Event;

/**
 * ElementEvent represents the parameter needed by [[Element]] events.
 *
 * @author Robin Schambach
 */
class ElementEvent extends Event
{
    /**
     * @var ElementInterface|null The element model associated with the event.
     */
    public $element;

    /**
     * @var bool Whether the element is brand new
     */
    public $isNew = false;
}
