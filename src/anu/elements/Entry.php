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
use anu\db\Exception;
use anu\elements\db\EntryQuery;
use anu\helper\ArrayHelper;
use anu\models\EntryType;
use anu\models\Section;
use anu\records\EntryRecord;

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
    private $_section;

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
     * @return \anu\base\ElementInterface|array
     */
    public function getAuthor()
    {
        return User::find()->id($this->authorId)->one();
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
        if ($this->_section !== null) {
            return $this->_section;
        }

        if ($this->sectionId === null) {
            throw new InvalidConfigException('Entry is missing its section ID');
        }

        if (($section = Anu::$app->getSections()->getSectionById($this->sectionId)) === null) {
            throw new InvalidConfigException('Invalid section ID: ' . $this->sectionId);
        }

        $this->_section = $section;

        return $section;
    }

    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     * @throws Exception if reasons
     */
    public function afterSave(bool $isNew)
    {
        $section = $this->getSection();

        // Get the entry record
        if (!$isNew) {
            $record = EntryRecord::findOne($this->id);

            if (!$record) {
                throw new Exception('Invalid entry ID: ' . $this->id);
            }
        } else {
            $record = new EntryRecord();
            $record->id = $this->id;
        }

        $record->sectionId = $this->sectionId;
        $record->typeId = $this->typeId;
        $record->authorId = $this->authorId;
        $record->postDate = $this->postDate;
        $record->expiryDate = $this->expiryDate;
        $record->save(false);

        /*
        if ($section->type == Section::TYPE_STRUCTURE) {
            // Has the parent changed?
            if ($this->_hasNewParent()) {
                if (!$this->newParentId) {
                    Craft::$app->getStructures()->appendToRoot($section->structureId, $this);
                } else {
                    Craft::$app->getStructures()->append($section->structureId, $this, $this->getParent());
                }
            }

            // Update the entry's descendants, who may be using this entry's URI in their own URIs
            Craft::$app->getElements()->updateDescendantSlugsAndUris($this, true, true);
        }
        */

        parent::afterSave($isNew);
    }

    /**
     * @return array
     * @throws \anu\base\InvalidConfigException
     */
    public function jsonSerialize()
    {
        $attributes = $this->getAttributes();
        $attributes['section'] = $this->getSection();
        $attributes['type'] = $this->getType();

        return $attributes;
    }

}