<?php

namespace anu\db;

use anu\base\Event;

/**
 * AfterSaveEvent represents the information available in [[ActiveRecord::EVENT_AFTER_INSERT]] and [[ActiveRecord::EVENT_AFTER_UPDATE]].
 *
 * @author Robin Schambach
 */
class AfterSaveEvent extends Event
{
    /**
     * @var array The attribute values that had changed and were saved.
     */
    public $changedAttributes;
}
