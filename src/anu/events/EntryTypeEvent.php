<?php

namespace anu\events;

use anu\base\Event;
use anu\models\EntryType;
use anu\models\Section;

/**
 * EntryTypeEvent represents the parameter needed by [[Section]] events.
 *
 * @author Robin Schambach
 */
class EntryTypeEvent extends Event
{
    /**
     * @var EntryType|null The element model associated with the event.
     */
    public $entryType;

    /**
     * @var bool Whether the element is brand new
     */
    public $isNew = false;
}
