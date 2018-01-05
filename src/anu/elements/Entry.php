<?php
/**
 * Created by PhpStorm.
 * User: scham
 * Date: 04.01.2018
 * Time: 14:21
 */

namespace anu\elements;

use Anu;
use anu\base\Element;
use anu\base\InvalidConfigException;
use anu\elements\db\EntryQuery;
use anu\helper\ArrayHelper;
use anu\models\EntryType;
use anu\models\Section;

class Entry extends Element
{
    /**
     * @var integer Id of the section of the element
     */
    public $sectionId;
    public $expiryDate;
    public $postDate;
    public $authorId;
    public $typeId;
    /**
     * @var int|null New parent ID
     */
    public $newParentId;

    public static function find()
    {
        return new EntryQuery(['type' => static::class]);
    }

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Anu::t('anu', 'Entries');
    }

    /**
     * Returns the type of entry.
     *
     * @return \anu\models\EntryType
     * @throws InvalidConfigException if [[typeId]] is missing or invalid
     */
    public function getType(): EntryType
    {
        if ($this->typeId === null) {
            throw new InvalidConfigException('Entry is missing its type ID');
        }

        $sectionEntryTypes = ArrayHelper::index($this->getSection()->getEntryTypes(), 'id');

        if (!isset($sectionEntryTypes[$this->typeId])) {
            throw new InvalidConfigException('Invalid entry type ID: ' . $this->typeId);
        }

        return $sectionEntryTypes[$this->typeId];
    }

    /**
     * Returns the entry's section.
     *
     * @return \anu\models\Section
     * @throws \anu\base\InvalidConfigException
     */
    public function getSection(): Section
    {
        if ($this->sectionId === null) {
            throw new InvalidConfigException('Entry is missing its section ID');
        }

        if (($section = Anu::$app->getSections()->getSectionById($this->sectionId)) === null) {
            throw new InvalidConfigException('Invalid section ID: ' . $this->sectionId);
        }

        return $section;
    }
}