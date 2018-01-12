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
use anu\base\FieldTrait;
use anu\base\Model;
use anu\base\SavableComponent;
use anu\db\Schema;
use anu\elements\db\ElementQuery;
use anu\elements\db\ElementQueryInterface;
use anu\events\FieldElementEvent;
use anu\records\FieldGroupRecord;
use anu\records\FieldRecord;
use anu\validators\UniqueValidator;

class Field extends SavableComponent implements FieldInterface{
    // Traits
    // =========================================================================

    use FieldTrait;

    // Constants
    // =========================================================================

    // Events
    // -------------------------------------------------------------------------

    /**
     * @event FieldElementEvent The event that is triggered before the element is saved
     *
     * set [[FieldElementEvent::isValid]] to `false` to prevent the element from getting saved.
     */
    public const EVENT_BEFORE_ELEMENT_SAVE = 'beforeElementSave';

    /**
     * @event FieldElementEvent The event that is triggered after the element is saved
     */
    public const EVENT_AFTER_ELEMENT_SAVE = 'afterElementSave';

    /**
     * @event FieldElementEvent The event that is triggered before the element is deleted
     *
     * set [[FieldElementEvent::isValid]] to `false` to prevent the element from getting deleted.
     */
    public const EVENT_BEFORE_ELEMENT_DELETE = 'beforeElementDelete';

    /**
     * @event FieldElementEvent The event that is triggered after the element is deleted
     */
    public const EVENT_AFTER_ELEMENT_DELETE = 'afterElementDelete';


    public $settings = [];

    /**
     * @var mixed
     */
    public $value;

    /**
     * @var string $type class of the field
     */
    public $type;

    /**
     * @param Model $element
     *
     * @return mixed
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
     * @param                 $value
     * @param \anu\base\Model $element
     *
     * @return string
     */
    public function getInputHtml($value, Model $element = null): string{
        return '';
    }

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     */
    public function modifyElementsQuery(ElementQueryInterface $query, $value)
    {
        if ($value !== null) {
            // If the field type doesn't have a content column, it *must* override this method
            // if it wants to support a custom query criteria attribute
            if (!static::hasContentColumn()) {
                return false;
            }

            $handle = $this->handle;
            /** @var \anu\elements\db\ElementQuery $query */
            $query->subQuery->andWhere(['content.' . \Anu::$app->getContent()->fieldColumnPrefix . $handle => $value]);
        }

        return null;
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
    public function normalizeValue($value, ElementInterface $element = null)
    {
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
        // If the object explicitly defines its savable value, use that
        if ($value instanceof \Serializable) {
            return $value->serialize();
        }

        // If it's "arrayable", convert to array
        if ($value instanceof Arrayab) {
            return $value->toArray();
        }

        return $value;
    }

    /**
     * Returns the field’s group.
     *
     * @return FieldGroupRecord
     */
    public function getGroup(): FieldGroupRecord
    {
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
    /**
     * @inheritdoc
     */
    public function beforeElementSave(ElementInterface $element, bool $isNew): bool
    {
        // Trigger a 'beforeElementSave' event
        $event = new FieldElementEvent(
            [
                'element' => $element,
                'isNew'   => $isNew,
            ]
        );
        $this->trigger(self::EVENT_BEFORE_ELEMENT_SAVE, $event);

        return $event->isValid;
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
        // Trigger an 'afterElementSave' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_ELEMENT_SAVE)) {
            $this->trigger(
                self::EVENT_AFTER_ELEMENT_SAVE,
                new FieldElementEvent(
                    [
                        'element' => $element,
                        'isNew'   => $isNew,
                    ]
                )
            );
        }
    }

    /**
     * Performs actions before an element is deleted.
     *
     * @param ElementInterface $element The element that is about to be deleted
     *
     * @return bool Whether the element should be deleted
     */
    public function beforeElementDelete(ElementInterface $element): bool{
        // Trigger a 'beforeElementDelete' event
        $event = new FieldElementEvent(
            [
                'element' => $element,
            ]
        );
        $this->trigger(self::EVENT_BEFORE_ELEMENT_DELETE, $event);

        return $event->isValid;
    }

    /**
     * Performs actions after the element has been deleted.
     *
     * @param ElementInterface $element The element that was just deleted
     *
     * @return void
     */
    public function afterElementDelete(ElementInterface $element): void
    {
        // Trigger an 'afterElementDelete' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_ELEMENT_DELETE)) {
            $this->trigger(
                self::EVENT_AFTER_ELEMENT_DELETE,
                new FieldElementEvent(
                    [
                        'element' => $element,
                    ]
                )
            );
        }
    }

    public function init(){
        if($this->settings !== null && is_string($this->settings)){
            $this->settings = json_decode($this->settings);
        }
    }
}