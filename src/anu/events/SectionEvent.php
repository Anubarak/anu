<?php

namespace anu\events;

use anu\base\Event;
use anu\models\Section;

/**
 * SectionEvent represents the parameter needed by [[Section]] events.
 *
 * @author Robin Schambach
 */
class SectionEvent extends Event
{
    /**
     * @var Section|null The element model associated with the event.
     */
    public $section;

    /**
     * @var bool Whether the element is brand new
     */
    public $isNew = false;
}
