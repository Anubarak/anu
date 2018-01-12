<?php
/**
 * Created by PhpStorm.
 * User: scham
 * Date: 10.01.2018
 * Time: 14:59
 */

namespace anu\events;

class PopulateElementEvent extends ElementEvent
{
    // Properties
    // =========================================================================

    /**
     * @var array|null The element query’s result for this element.
     */
    public $row;
}