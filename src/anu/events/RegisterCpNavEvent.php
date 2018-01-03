<?php

namespace anu\events;

use anu\base\Event;

/**
 * Register CP navigation items
 *
 * @author Robin Schambach
 */
class RegisterCpNavEvent extends Event
{

    /**
     * @var array items all registered items from the cp
     */
    public $items;
}
