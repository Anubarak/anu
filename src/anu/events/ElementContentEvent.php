<?php
/**
 * Created by PhpStorm.
 * User: scham
 * Date: 10.01.2018
 * Time: 14:19
 */

namespace anu\events;


use anu\base\Event;

class ElementContentEvent extends Event
{
    // Properties
    // =========================================================================

    /**
     * @var \anu\base\ElementInterface|null The element model associated with the event.
     */
    public $element;
}
