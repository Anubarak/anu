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
use anu\base\Exception;
use anu\base\FieldInterface;
use anu\base\FieldNotFoundException;
use anu\db\Query;
use anu\events\FieldEvent;
use anu\events\FieldLayoutEvent;
use anu\events\RegisterFieldTypesEvent;
use anu\fields\NumberField;
use anu\fields\TextField;
use anu\models\Field;
use anu\models\FieldLayout;
use anu\models\FieldLayoutTab;
use anu\records\FieldGroupRecord;
use anu\records\FieldLayoutField;
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
    public const EVENT_BEFORE_SAVE_FIELD = 'beforeSaveField';

    /**
     * Register field type event
     */
    public const REGISTER_FIELD_TYPES = 'registerFieldTypes';
    /**
     * @event FieldLayoutEvent The event that is triggered before a field layout is saved.
     */
    public const EVENT_BEFORE_SAVE_FIELD_LAYOUT = 'beforeSaveFieldLayout';
    /**
     * @event FieldLayoutEvent The event that is triggered after a field layout is saved.
     */
    public const EVENT_AFTER_SAVE_FIELD_LAYOUT = 'afterSaveFieldLayout';
    /**
     * @var string
     */
    public $oldFieldColumnPrefix = 'field_';
    /**
     * @var
     */
    private $_layoutsById;

    /**
     * @param FieldInterface $field
     * @param bool $runValidation
     * @return bool
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\db\Exception
     * @throws \Throwable
     */
    public function saveField(FieldInterface $field, bool $runValidation = true): bool
    {

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
    public function getAllFields(): array
    {
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
     * Creates a field with a given config.
     *
     * @param mixed $config The field’s class name, or its config, with a `type` value and optionally a `settings` value
     *
     * @return FieldInterface The field
     * @throws \anu\base\InvalidConfigException
     */
    public function createField($config): FieldInterface
    {
        if (\is_string($config)) {
            $config = ['type' => $config];
        }

        /** @var Field $field */
        $field = Anu::$container->get($config['type'], [], $config);
        $field->setAttributes($config, false);

        return $field;
    }

    /**
     * Get all Field groups
     *
     * @return \anu\db\ActiveRecord[]
     * @throws \anu\base\InvalidConfigException
     */
    public function getAllGroups(): array
    {
        $allGroups = FieldGroupRecord::find()->all();

        Anu::$app->getTemplate()->addAnuJsObject($allGroups, 'fieldGroups');

        return $allGroups ?: [];
    }

    /**
     * @return Field[]|array
     */
    public function getAllFieldTypes(): array
    {
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
     * @param $id
     *
     * @return object
     * @throws \anu\base\InvalidConfigException
     */
    public function getFieldById($id)
    {
        $result = $this->_createFieldQuery()->where(['fields.id' => $id])->one();
        $class = Anu::$app->getRequest()->getParam('type', $result['type']);

        /** @var Field $field */
        return Anu::$container->get($class, [], $result);
    }

    /**
     * Returns all the fields in a given group.
     *
     * @param int $groupId The field group’s ID
     *
     * @return FieldInterface[] The fields
     * @throws \anu\base\InvalidConfigException
     */
    public function getFieldsByGroupId(int $groupId): array
    {
        $results = $this->_createFieldQuery()->where(['fields.groupId' => $groupId])->all();

        $fields = [];

        foreach ($results as $result) {
            $fields[] = $this->createField($result);
        }

        return $fields;
    }

    /**
     * @param $groupId
     *
     * @return \anu\models\Field[]
     * @throws \anu\base\InvalidConfigException
     */
    public function getFieldsForGroup($groupId): array
    {
        $result = $this->_createFieldQuery()->where(['fields.groupId' => $groupId])->all();
        $fields = [];
        foreach ($result as $fieldRows) {
            $fields[] = Anu::$container->get($fieldRows['type'], [], $fieldRows);
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function getAllFieldNames(): array
    {
        $fieldNames = [];
        foreach($this->getAllFieldTypes() as $fieldClass){
            $fieldNames[$fieldClass] = $fieldClass::getFieldLabel();
        }

        return $fieldNames;
    }

    /**
     * @param FieldGroupRecord $groupRecord
     *
     * @return bool
     * @throws \anu\base\InvalidParamException
     */
    public function saveGroup(FieldGroupRecord $groupRecord): bool
    {
        if($groupRecord->validate() === false){
            return false;
        }

        return $groupRecord->save(false);
    }

    /**
     * @return \anu\models\FieldLayout
     * @throws \anu\base\InvalidConfigException
     */
    public function assembleLayoutFromPost(): FieldLayout
    {
        $request = Anu::$app->getRequest();
        $postedFieldLayout = $request->getParam('fieldLayout', []);

        /** @var string $postedFieldLayout */
        $fieldLayout = $this->assembleLayout(json_decode($postedFieldLayout, true));
        $fieldLayout->id = (int) $request->getParam('fieldLayoutId');

        return $fieldLayout;
    }

    /**
     * Assembles a field layout.
     *
     * @param array $postedFieldLayout The post data for the field layout
     * @param array $requiredFields    The field IDs that should be marked as required in the field layout
     *
     * @return FieldLayout The field layout
     * @throws \anu\base\InvalidConfigException
     */
    public function assembleLayout(array $postedFieldLayout, array $requiredFields = []): FieldLayout
    {
        $tabs = [];
        $fields = [];

        $tabSortOrder = 0;

        // Get all the fields
        $allFieldIds = [];

        $allFieldsById = [];

        foreach ($postedFieldLayout['tabs'] as $groups) {
            foreach ($groups['fields'] as $field) {
                if (\is_array($field)) {
                    $allFieldIds[] = $field['id'];
                } else {
                    $allFieldIds[] = $field;
                }
            }
        }

        if (!empty($allFieldIds)) {
            $allFieldsById = [];

            $results = $this->_createFieldQuery()->where(['id' => $allFieldIds])->all();

            foreach ($results as $result) {
                $allFieldsById[$result['id']] = Anu::$container->get($result['type'], [], $result);;
            }
        }

        foreach ($postedFieldLayout['tabs'] as $tabGroup) {
            /** @var array $tab */
            /** @var array $fieldIds */
            $fieldIds = [];
            foreach ($tabGroup['fields'] as $field) {
                if (\is_array($field)) {
                    $fieldIds[] = $field['id'];
                }
            }


            $tabFields = [];
            $tabSortOrder++;

            foreach ($fieldIds as $fieldSortOrder => $fieldId) {
                if (!isset($allFieldsById[$fieldId])) {
                    continue;
                }

                $field = $allFieldsById[$fieldId];
                $field->required = \in_array($fieldId, $requiredFields, false);
                $field->sortOrder = ($fieldSortOrder + 1);

                $fields[] = $field;
                $tabFields[] = $field;
            }

            $tab = new FieldLayoutTab();
            $tab->name = urldecode($tabGroup['name']);
            $tab->sortOrder = $tabSortOrder;
            $tab->setFields($tabFields);

            $tabs[] = $tab;
        }

        $layout = new FieldLayout();
        $layout->setTabs($tabs);
        $layout->setFields($fields);

        return $layout;
    }

    /**
     * Saves a field layout.
     *
     * @param FieldLayout $layout The field layout
     * @param bool        $runValidation Whether the layout should be validated
     *
     * @return bool Whether the field layout was saved successfully
     * @throws \anu\base\InvalidParamException
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\Exception
     */
    public function saveLayout(FieldLayout $layout, bool $runValidation = true): bool
    {
        $isNewLayout = !$layout->id;

        // Make sure the tabs/fields are memoized on the layout
        foreach ($layout->getTabs() as $tab) {
            $tab->getFields();
        }

        // Fire a 'beforeSaveFieldLayout' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_FIELD_LAYOUT)) {
            $this->trigger(
                self::EVENT_BEFORE_SAVE_FIELD_LAYOUT,
                new FieldLayoutEvent(
                    [
                        'layout' => $layout,
                        'isNew'  => $isNewLayout,
                    ]
                )
            );
        }

        if ($runValidation && !$layout->validate()) {
            return false;
        }

        if (!$isNewLayout) {
            // Delete the old tabs/fields
            Anu::$app->getDb()->createCommand()->delete('{{%fieldlayouttabs}}', ['layoutId' => $layout->id])->execute();

            // Get the current layout
            if (($layoutRecord = \anu\records\FieldLayout::findOne($layout->id)) === null) {
                throw new Exception('Invalid field layout ID: ' . $layout->id);
            }
        } else {
            $layoutRecord = new \anu\records\FieldLayout();
        }

        $layoutRecord->type = $layout->type;

        if (!$isNewLayout) {
            $layoutRecord->id = $layout->id;
        }

        // Save it
        $layoutRecord->save(false);

        if ($isNewLayout) {
            $layout->id = $layoutRecord->id;
        }

        foreach ($layout->getTabs() as $tab) {
            $tabRecord = new \anu\records\FieldLayoutTab();
            $tabRecord->layoutId = $layout->id;
            $tabRecord->name = $tab->name;
            $tabRecord->sortOrder = $tab->sortOrder;
            $tabRecord->save(false);
            $tab->id = $tabRecord->id;

            foreach ($tab->getFields() as $field) {
                /** @var Field $field */
                $fieldRecord = new FieldLayoutField();
                $fieldRecord->layoutId = $layout->id;
                $fieldRecord->tabId = $tab->id;
                $fieldRecord->fieldId = $field->id;
                $fieldRecord->required = $field->required;
                $fieldRecord->sortOrder = $field->sortOrder;
                $fieldRecord->save(false);
            }
        }

        // Fire an 'afterSaveFieldLayout' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_FIELD_LAYOUT)) {
            $this->trigger(
                self::EVENT_AFTER_SAVE_FIELD_LAYOUT,
                new FieldLayoutEvent(
                    [
                        'layout' => $layout,
                        'isNew'  => $isNewLayout,
                    ]
                )
            );
        }

        return true;
    }

    /**
     * Returns a field layout by its ID.
     *
     * @param int $layoutId The field layout’s ID
     *
     * @return FieldLayout|null The field layout, or null if it doesn’t exist
     */
    public function getLayoutById(int $layoutId): ?FieldLayout
    {
        if ($this->_layoutsById !== null && array_key_exists($layoutId, $this->_layoutsById)) {
            return $this->_layoutsById[$layoutId];
        }

        $result = $this->_createLayoutQuery()->where(['id' => $layoutId])->one();

        return $this->_layoutsById[$layoutId] = $result ? new FieldLayout($result) : null;
    }

    /**
     * Returns a layout's tabs by its ID.
     *
     * @param int $layoutId The field layout’s ID
     *
     * @return FieldLayoutTab[] The field layout’s tabs
     */
    public function getLayoutTabsById(int $layoutId): array
    {
        $tabs = $this->_createLayoutTabQuery()->where(['layoutId' => $layoutId])->all();

        foreach ($tabs as $key => $value) {
            $tabs[$key] = new FieldLayoutTab($value);
        }

        return $tabs;
    }

    /**
     * Returns the fields in a field layout, identified by its ID.
     *
     * @param int $layoutId The field layout’s ID
     *
     * @return FieldInterface[] The fields
     * @throws \anu\base\InvalidConfigException
     */
    public function getFieldsByLayoutId(int $layoutId): array
    {
        $fields = [];

        $results = $this->_createFieldQuery()->addSelect(
                [
                    'flf.layoutId',
                    'flf.tabId',
                    'flf.required',
                    'flf.sortOrder',
                ]
            )->innerJoin('{{%fieldlayoutfields}} flf', '[[flf.fieldId]] = [[fields.id]]')->innerJoin('{{%fieldlayouttabs}} flt', '[[flt.id]] = [[flf.tabId]]')->where(
                ['flf.layoutId' => $layoutId]
            )->orderBy(['flt.sortOrder' => SORT_ASC, 'flf.sortOrder' => SORT_ASC])->all();

        foreach ($results as $result) {
            $fields[] = $this->createField($result);
        }

        return $fields;
    }

    // private functions
    // ======================================================================

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

    /**
     * Returns a Query object prepped for retrieving layouts.
     *
     * @return Query
     */
    private function _createLayoutQuery(): Query
    {
        return (new Query)->select(
                [
                    'id',
                    'type',
                ]
            )->from(['{{%fieldlayouts}}']);
    }

    /**
     * Returns a Query object prepped for retrieving layout tabs.
     *
     * @return Query
     */
    private function _createLayoutTabQuery(): Query
    {
        return (new Query())->select(
                [
                    'id',
                    'layoutId',
                    'name',
                    'sortOrder',
                ]
            )->from(['{{%fieldlayouttabs}}'])->orderBy(['sortOrder' => SORT_ASC]);
    }

}