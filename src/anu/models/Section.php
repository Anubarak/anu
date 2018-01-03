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

    const TYPE_SINGLE = 'single';
    const TYPE_CHANNEL = 'channel';
    const TYPE_STRUCTURE = 'structure';


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


    public function rules(){
        return [
            //['handle', HandleValidator::class],
            [['name', 'handle', 'type'], 'required'],
            ['handle', UniqueValidator::className(), 'targetClass' => SectionRecord::className()],
        ];
    }

    /**
     * @return \anu\db\ActiveRecord[]|array
     */
    public function getEntryTypes(){
        return EntryTypeRecord::find()->where(['sectionId' => $this->id])->all();
    }
}