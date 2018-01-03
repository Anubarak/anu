<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 29.12.2017
 * Time: 09:49
 */

namespace anu\service;
use Anu;
use anu\base\Component;
use anu\base\FieldInterface;
use anu\base\FieldNotFoundException;
use anu\db\Query;
use anu\events\FieldEvent;
use anu\events\RegisterFieldTypesEvent;
use anu\fields\NumberField;
use anu\fields\TextField;
use anu\models\Field;
use anu\records\FieldGroupRecord;
use anu\records\FieldRecord;
use Twig\Node\AutoEscapeNode;

class Fields extends Component{

    /**
     * @var array $_fieldTypes;
     */
    private $_fieldTypes;

    /**
     * @var $_fieldRecordsById FieldRecord[] cache field records by id
     */
    private $_fieldRecordsById;

    /**
     * @var $_fieldModelById Field[]
     */
    private $_fieldModelById;

    /**
     * Event before the field is saved
     */
    const EVENT_BEFORE_SAVE_FIELD = 'beforeSaveField';

    /**
     * Register field type event
     */
    const REGISTER_FIELD_TYPES = 'registerFieldTypes';

    /**
     * @var string
     */
    public $oldFieldColumnPrefix = 'field_';

    /**
     * @param FieldInterface $field
     * @param bool $runValidation
     * @return bool
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\db\Exception
     * @throws \Throwable
     */
    public function saveField(FieldInterface $field, bool $runValidation = true){

        /** @var Field $field */
        $isNewField = $field->getIsNew();

        // Fire a 'beforeSaveField' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_FIELD)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_FIELD, new FieldEvent([
                'field' => $field,
                'isNew' => $isNewField,
            ]));
        }


        if ($runValidation && !$field->validate()) {
            return false;
        }
        $transaction = Anu::$app->getDb()->beginTransaction();
        try {
            $fieldRecord = $this->_getFieldRecord($field);

            // Create/alter the content table column
            $contentTable = Anu::$app->getContent()->contentTable;
            $oldColumnName = $this->oldFieldColumnPrefix.$fieldRecord->getOldHandle();
            $newColumnName = Anu::$app->getContent()->fieldColumnPrefix.$field->handle;

            if ($field::hasContentColumn()) {
                $columnType = $field->getContentColumnType();

                // Make sure we're working with the latest data in the case of a renamed field.
                Anu::$app->getDb()->schema->refresh();

                if (Anu::$app->getDb()->columnExists($contentTable, $oldColumnName)) {
                    Anu::$app->getDb()->createCommand()
                        ->alterColumn($contentTable, $oldColumnName, $columnType)
                        ->execute();
                    if ($oldColumnName !== $newColumnName) {
                        Anu::$app->getDb()->createCommand()
                            ->renameColumn($contentTable, $oldColumnName, $newColumnName)
                            ->execute();
                    }
                } else if (Anu::$app->getDb()->columnExists($contentTable, $newColumnName)) {
                    Anu::$app->getDb()->createCommand()
                        ->alterColumn($contentTable, $newColumnName, $columnType)
                        ->execute();
                } else {
                    Anu::$app->getDb()->createCommand()
                        ->addColumn($contentTable, $newColumnName, $columnType)
                        ->execute();
                }
            } else {
                // Did the old field have a column we need to remove?
                if (
                    !$isNewField &&
                    $fieldRecord->getOldHandle() &&
                    Anu::$app->getDb()->columnExists($contentTable, $oldColumnName)
                ) {
                    Anu::$app->getDb()->createCommand()
                        ->dropColumn($contentTable, $oldColumnName)
                        ->execute();
                }
            }


            $fieldRecord->groupId = $field->groupId;
            $fieldRecord->name = $field->name;
            $fieldRecord->handle = $field->handle;
            $fieldRecord->instructions = $field->instructions;
            $fieldRecord->type = get_class($field);
            $fieldRecord->settings = $field->getSettings();

            $fieldRecord->save(false);
            // Now that we have a field ID, save it on the model
            if ($isNewField) {
                $field->id = $fieldRecord->id;
            } else {
                // Save the old field handle on the model in case the field type needs to do something with it.
                $field->oldHandle = $fieldRecord->getOldHandle();
            }

        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        return true;

    }

    /**
     * @return Field[] get All fields
     * @throws \anu\base\InvalidConfigException
     */
    public function getAllFields(){
        if($this->_fieldModelById !== null){
            return $this->_fieldModelById;
        }

        $allRecords = FieldRecord::find()->orderBy('name')->all();
        $models = [];
        foreach($allRecords as $record){
            $this->_fieldRecordsById[$record->id] = $record;
            $model = new Field($record->getAttributes());
            $this->_fieldModelById[$model->id] = $model;
            $models[] = $model;
        }

        Anu::$app->getTemplate()->addAnuJsObject($models, 'fields');

        return $this->_fieldModelById;
    }

    /**
     * @param $id
     * @return Field
     * @throws \anu\base\InvalidConfigException
     */
    public function getFieldById($id){
        $result = $this->_createFieldQuery()
            ->where(['fields.id' => $id])
            ->one();

        /** @var Field $field */
        $field = $this->createField($result);

        return $field;
    }

    /**
     * Creates a field with a given config.
     *
     * @param mixed $config The field’s class name, or its config, with a `type` value and optionally a `settings` value
     *
     * @return FieldInterface The field
     * @throws \anu\base\InvalidConfigException
     */
    public function createField($config): FieldInterface
    {
        if (is_string($config)) {
            $config = ['type' => $config];
        }

        /** @var Field $field */
        $field = Anu::createObject($config['type'], $config, true);
        $field->setAttributes($config, false);

        return $field;
    }

    /**
     * Get all Field groups
     *
     * @return \anu\db\ActiveRecord[]
     * @throws \anu\base\InvalidConfigException
     */
    public function getAllGroups(){
        $allGroups = FieldGroupRecord::find()->all();

        Anu::$app->getTemplate()->addAnuJsObject($allGroups, 'fieldGroups');

        return $allGroups? $allGroups : [];
    }

    /**
     * @return Field[]|array
     */
    public function getAllFieldTypes(){
        if($this->_fieldTypes !== null){
            return $this->_fieldTypes;
        }

        $this->trigger(self::REGISTER_FIELD_TYPES, $fields = new RegisterFieldTypesEvent([
            'fields'    => [
                TextField::className(),
                NumberField::className()
            ]
        ]) );

        $this->_fieldTypes = $fields->fields;

        return $this->_fieldTypes;
    }

    /**
     * @return array
     */
    public function getAllFieldNames(){
        $fieldNames = [];
        foreach($this->getAllFieldTypes() as $fieldClass){
            $fieldNames[$fieldClass] = $fieldClass::getFieldLabel();
        }

        return $fieldNames;
    }


    /**
     * @param FieldGroupRecord $groupRecord
     */
    public function saveGroup(FieldGroupRecord $groupRecord){
        if($groupRecord->validate() === false){
            return false;
        }

        return $groupRecord->save(false);
    }


    /**
     * Returns a field record for a given model.
     *
     * @param FieldInterface $field
     *
     * @return FieldRecord
     * @throws FieldNotFoundException if $field->id is invalid
     */
    private function _getFieldRecord(FieldInterface $field): FieldRecord
    {
        /** @var Field $field */
        if ($field->getIsNew()) {
            return new FieldRecord();
        }

        if ($this->_fieldRecordsById !== null && array_key_exists($field->id, $this->_fieldRecordsById)) {
            return $this->_fieldRecordsById[$field->id];
        }

        if (($this->_fieldRecordsById[$field->id] = FieldRecord::findOne($field->id)) === null) {
            throw new FieldNotFoundException('Invalid field ID: '.$field->id);
        }

        return $this->_fieldRecordsById[$field->id];
    }

    /**
     * Returns a Query object prepped for retrieving fields.
     *
     * @return Query
     */
    private function _createFieldQuery(): Query
    {
        return (new Query())
            ->select([
                'fields.id',
                'fields.dateCreated',
                'fields.dateUpdated',
                'fields.groupId',
                'fields.name',
                'fields.handle',
                'fields.instructions',
                'fields.type',
                'fields.settings'
            ])
            ->from(['{{%fields}} fields'])
            ->orderBy(['fields.name' => SORT_ASC]);
    }

}