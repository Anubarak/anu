<?php
namespace anu\base;



/**
 * FieldInterface defines the common interface to be implemented by field classes.
 *
 *
 * @author Robin Schambach
 */
interface FieldInterface
{

    // Static
    // =========================================================================

    public static function getFieldLabel(): string;

    /**
     * Returns whether this field has a column in the content table.
     *
     * @return bool
     */
    public static function hasContentColumn(): bool;


    // Public
    // =========================================================================

    public function getSettings(): array ;

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
    public function getContentColumnType(): string;

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
    public function getInputHtml($value, ElementInterface $element = null): string;

    /**
     * Normalizes the field’s value for use.
     *
     * This method is called by `entry.fieldHandle` to output the value
     *
     *
     * @param mixed                 $value   The raw field value
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return mixed The prepared field value
     */
    public function normalizeValue($value, ElementInterface $element = null);

    /**
     * Prepares the field’s value to be stored somewhere, like the content table or JSON-encoded in an entry revision table.
     *
     * Data types that are JSON-encodable are safe (arrays, integers, strings, booleans, etc).
     *
     * Whatever this returns should be something [[normalizeValue()]] can handle.
     *
     * @param mixed                 $value   The raw field value
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return mixed The serialized field value
     */
    public function serializeValue($value, ElementInterface $element = null);

    /**
     * Returns the field’s group.
     *
     * @return FieldGroup|null
     */
    public function getGroup();

    /**
     * Performs actions before an element is saved.
     *
     * @param ElementInterface $element The element that is about to be saved
     * @param bool             $isNew   Whether the element is brand new
     *
     * @return bool Whether the element should be saved
     */
    public function beforeElementSave(ElementInterface $element, bool $isNew): bool;

    /**
     * Performs actions after the element has been saved.
     *
     * @param ElementInterface $element The element that was just saved
     * @param bool             $isNew   Whether the element is brand new
     *
     * @return void
     */
    public function afterElementSave(ElementInterface $element, bool $isNew);

    /**
     * Performs actions before an element is deleted.
     *
     * @param ElementInterface $element The element that is about to be deleted
     *
     * @return bool Whether the element should be deleted
     */
    public function beforeElementDelete(ElementInterface $element): bool;

    /**
     * Performs actions after the element has been deleted.
     *
     * @param ElementInterface $element The element that was just deleted
     *
     * @return void
     */
    public function afterElementDelete(ElementInterface $element);
}
