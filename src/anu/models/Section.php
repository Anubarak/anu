<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 24.12.2017
 * Time: 11:13
 */

namespace anu\models;

use anu\base\ElementInterface;
use anu\base\FieldGroup;
use anu\base\FieldInterface;
use anu\base\Model;
use anu\base\SavableComponent;
use anu\db\Schema;
use anu\records\EntryTypeRecord;
use anu\records\FieldGroupRecord;
use anu\records\FieldRecord;
use anu\records\SectionRecord;
use anu\validators\UniqueValidator;

class Section extends SavableComponent{

    // Constants
    // =========================================================================

    public const TYPE_SINGLE = 'single';
    public const TYPE_CHANNEL = 'channel';
    public const TYPE_STRUCTURE = 'structure';


    // Properties
    // -------------------------------------------------------------------------


    public $id;

    /**
     * @var string $handle The handle of the field, unique value to fetch the field
     */
    public $handle;

    /**
     * @var string $name The value that is displayed in the frontend
     */
    public $name;

    /**
     * @var string $type class of the field
     */
    public $type;
    // private properties
    // ===========================================================================

    private $_entryTypes;


    public function rules(){
        return [
            //['handle', HandleValidator::class],
            [['name', 'handle', 'type'], 'required'],
            ['handle', UniqueValidator::className(), 'targetClass' => SectionRecord::className()],
        ];
    }

    /**
     * Returns the section's entry types.
     *
     * @return EntryType[]
     * @throws \anu\base\InvalidConfigException
     */
    public function getEntryTypes(): array
    {
        if ($this->_entryTypes !== null) {
            return $this->_entryTypes;
        }

        if (!$this->id) {
            return [];
        }

        $this->_entryTypes = \Anu::$app->getSections()->getEntryTypesBySectionId($this->id);

        return $this->_entryTypes;
    }
}