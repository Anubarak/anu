<?php
/**
 * Created by PhpStorm.
 * User: scham
 * Date: 10.01.2018
 * Time: 14:31
 */

namespace anu\events;


use anu\base\ModelEvent;

class FieldElementEvent extends ModelEvent
{
    // Properties
    // =========================================================================

    /**
     * @var \anu\base\ElementInterface|null The element associated with this event
     */
    public $element;
}