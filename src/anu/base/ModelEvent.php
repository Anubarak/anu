<?php

namespace anu\base;

/**
 * ModelEvent represents the parameter needed by [[Model]] events.
 *
 * @author Robin Schambach
 */
class ModelEvent extends Event
{
    /**
     * @var bool whether the model is in valid status. Defaults to true.
     * A model is in valid status if it passes validations or certain checks.
     */
    public $isValid = true;

    /**
     * @var bool whether the model is new or not
     */
    public $isNew = false;
}
