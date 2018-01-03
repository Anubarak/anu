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
use anu\records\FieldGroupRecord;
use anu\records\FieldRecord;
use anu\validators\UniqueValidator;

class Field extends SavableComponent implements FieldInterface{

    // Constants
    // =========================================================================

    // Events
    // -------------------------------------------------------------------------

    /**
     * @event FieldElementEvent The event that is triggered before the element is saved
     *
     * You may set [[FieldElementEvent::isValid]] to `false` to prevent the element from getting saved.
     */
    const EVENT_BEFORE_ELEMENT_SAVE = 'beforeElementSave';

    /**
     * @event FieldElementEvent The event that is triggered after the element is saved
     */
    const EVENT_AFTER_ELEMENT_SAVE = 'afterElementSave';

    /**
     * @event FieldElementEvent The event that is triggered before the element is deleted
     *
     * You may set [[FieldElementEvent::isValid]] to `false` to prevent the element from getting deleted.
     */
    const EVENT_BEFORE_ELEMENT_DELETE = 'beforeElementDelete';

    /**
     * @event FieldElementEvent The event that is triggered after the element is deleted
     */
    const EVENT_AFTER_ELEMENT_DELETE = 'afterElementDelete';

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
     * @var array $settings this is the array with all settings, it is stored serialized in the db
     */
    public $settings = [];

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var string $instructions
     */
    public $instructions;

    /**
     * @var string $type class of the field
     */
    public $type;

    /**
     * @var int $groupId the id of the group of the field
     */
    public $groupId;

    /**
     * @var $oldHandle string the old handle of the field
     */
    public $oldHandle;


    /**
     * @param Model $element
     */
    public function getFieldValue(Model $element){

        return $element->{$this->handle};
    }

    public function rules(){
        return [
            //['handle', HandleValidator::class],
            [['name', 'handle'], 'required'],
            ['handle', UniqueValidator::className(), 'targetClass' => FieldRecord::className()],
        ];
    }

    /**
     * @return array
     */
    public function getSettings(): array {
        return $this->settings;
    }

    /**
     * Returns the default field label when creating new fields
     *
     * @return string
     */
    public static function getFieldLabel():string {
        return '';
    }

    /**
     * Returns whether this field has a column in the content table.
     *
     * @return bool
     */
    public static function hasContentColumn(): bool{
        return true;
    }

    /**
     * Returns the column type that this field should get within the content table.
     *
     * This method will only be called if [[hasContentColumn()]] returns true.
     *
     * @return string The column type. [[\anu\db\QueryBuilder::getColumnType()]] will be called
     * to convert the give column type to the physical one. For example, `string` will be converted
     * as `varchar(255)` and `string(100)` becomes `varchar(100)`. `not null` will automatically be
     * appended as well.
     * @see \anu\db\QueryBuilder::getColumnType()
     */
    public function getContentColumnType(): string{
        return Schema::TYPE_STRING;
    }

    /**
     * Returns the html for the field in the cp
     *
     * ```php
     * public function getInputHtml($value, $element)
     * {
     *
     *     // Render and return the input template
     *     return Anu::$app->getTemplate()->renderTemplate('/path/to/template/_fieldinput', [
     *         'name'         => $name,
     *         'id'           => $id,
     *         'namespacedId' => $namespacedId,
     *         'value'        => $value
     *     ]);
     * }
     * ```
     *
     * @param $value
     * @param ElementInterface|null $element
     * @return string
     */
    public function getInputHtml($value, Model $element = null): string{
        return '';
    }

    /**
     * Normalizes the field’s value for use.
     *
     * This method is called by `entry.fieldHandle` to output the value
     *
     *
     * @param mixed $value The raw field value
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return mixed The prepared field value
     */
    public function normalizeValue($value, Model $element = null){
        return $value;
    }

    /**
     * Prepares the field’s value to be stored somewhere, like the content table or JSON-encoded in an entry revision table.
     *
     * Data types that are JSON-encodable are safe (arrays, integers, strings, booleans, etc).
     *
     * Whatever this returns should be something [[normalizeValue()]] can handle.
     *
     * @param mixed $value The raw field value
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return mixed The serialized field value
     */
    public function serializeValue($value, ElementInterface $element = null){
        return json_encode($value);
    }

    /**
     * Returns the field’s group.
     *
     * @return FieldGroupRecord
     */
    public function getGroup(){
        if($this->groupId){
            return FieldGroupRecord::findOne($this->groupId);
        }
        return null;
    }

    /**
     * Performs actions before an element is saved.
     *
     * @param ElementInterface $element The element that is about to be saved
     * @param bool $isNew Whether the element is brand new
     *
     * @return bool Whether the element should be saved
     */
    public function beforeElementSave(ElementInterface $element, bool $isNew): bool{
        // TODO: Implement beforeElementSave() method.
    }

    /**
     * Performs actions after the element has been saved.
     *
     * @param ElementInterface $element The element that was just saved
     * @param bool $isNew Whether the element is brand new
     *
     * @return void
     */
    public function afterElementSave(ElementInterface $element, bool $isNew){
        // TODO: Implement afterElementSave() method.
    }

    /**
     * Performs actions before an element is deleted.
     *
     * @param ElementInterface $element The element that is about to be deleted
     *
     * @return bool Whether the element should be deleted
     */
    public function beforeElementDelete(ElementInterface $element): bool{
        // TODO: Implement beforeElementDelete() method.
    }

    /**
     * Performs actions after the element has been deleted.
     *
     * @param ElementInterface $element The element that was just deleted
     *
     * @return void
     */
    public function afterElementDelete(ElementInterface $element){
        // TODO: Implement afterElementDelete() method.
    }

    public function init(){
        if($this->settings !== null && is_string($this->settings)){
            $this->settings = json_decode($this->settings);
        }
    }
}