<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 31.12.2017
 * Time: 13:08
 */

namespace anu\base;

/**
 * SavableComponentTrait implements the common methods and properties for savable component classes.
 *
 * @author Robin Schambach
 */
trait SavableComponentTrait
{
    // Properties
    // =========================================================================

    /**
     * @var int|string|null The component’s ID (could be a temporary one: "new:X")
     */
    public $id;

    /**
     * @var \DateTime|null The date that the component was created
     */
    public $dateCreated;

    /**
     * @var \DateTime|null The date that the component was last updated
     */
    public $dateUpdated;

    /**
     * @var string The unique Id of the element
     */
    public $uid;
}