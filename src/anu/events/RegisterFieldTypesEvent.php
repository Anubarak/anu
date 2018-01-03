<?php

namespace anu\events;

use anu\base\Event;
use anu\models\Field;

/**
 * Register Field types items
 *
 * @author Robin Schambach
 */
class RegisterFieldTypesEvent extends Event
{

    /**
     * @var Field[] items all registered items from the cp
     */
    public $fields;
}
