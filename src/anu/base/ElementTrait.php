<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.github.io/license/
 */

namespace anu\base;

use DateTime;

/**
 * ElementTrait implements the common methods and properties for element classes.
 *
 * @author Robin Schambach
 */
trait ElementTrait
{
    // Properties
    // =========================================================================

    /**
     * @var int|null The element’s ID
     */
    public $id;

    /**
     * @var string|null The element’s temporary ID (only used if the element's URI format contains {id})
     */
    public $tempId;

    /**
     * @var string|null The element’s UID
     */
    public $uid;

    /**
     * @var int|null The element’s field layout ID
     */
    public $fieldLayoutId;


    /**
     * @var int|null The element’s content row ID
     */
    public $contentId;

    /**
     * @var bool Whether the element is enabled
     */
    public $enabled = true;

    /**
     * @var bool Whether the element is archived
     */
    public $archived = false;


    /**
     * @var string|null The element’s title
     */
    public $title;

    /**
     * @var string|null The element’s slug
     */
    public $slug;

    /**
     * @var string|null The element’s URI
     */
    public $uri;

    /**
     * @var DateTime|null The date that the element was created
     */
    public $dateCreated;

    /**
     * @var DateTime|null The date that the element was last updated
     */
    public $dateUpdated;

    /**
     * @var int|null The element’s structure’s root ID
     */
    public $root;


    /**
     * @var int|null The element’s level within its structure
     */
    public $level;
}
