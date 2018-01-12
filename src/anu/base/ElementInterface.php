<?php
namespace anu\base;

use anu\elements\db\ElementQueryInterface;
use anu\models\FieldLayout;


/**
 * ElementInterface defines the common interface to be implemented by element classes.
 *
 * A class implementing this interface should also use [[ElementTrait]] and [[ContentTrait]].
 *
 * @author Robin Schambach
 */
interface ElementInterface
{

    // Static
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string;


    /**
     * Returns whether elements of this type will be storing any data in the `content` table (tiles or custom fields).
     *
     * @return bool Whether elements of this type will be storing any data in the `content` table.
     */
    public static function hasContent(): bool;

    /**
     * Returns whether elements of this type have traditional titles.
     *
     * @return bool Whether elements of this type have traditional titles.
     */
    public static function hasTitles(): bool;


    /**
     * Creates an [[ElementQueryInterface]] instance for query purpose.
     *
     * The returned [[ElementQueryInterface]] instance can be further customized by calling
     * methods defined in [[ElementQueryInterface]] before `one()` or `all()` is called to return
     * populated [[ElementInterface]] instances. For example,
     *
     * ```php
     * // Find the entry whose ID is 5
     * $entry = Entry::find()->id(5)->one();
     *
     * // Find all assets and order them by their filename:
     * $assets = Asset::find()
     *     ->orderBy('filename')
     *     ->all();
     * ```
     *
     * If you want to define custom criteria parameters for your elements, you can do so by overriding
     * this method and returning a custom query class. For example,
     *
     * ```php
     * class Product extends Element
     * {
     *     public static function find()
     *     {
     *         // use ProductQuery instead of the default ElementQuery
     *         return new ProductQuery(get_called_class());
     *     }
     * }
     * ```
     *
     * You can also set default criteria parameters on the ElementQuery if you don’t have a need for
     * a custom query class. For example,
     *
     * ```php
     * class Customer extends ActiveRecord
     * {
     *     public static function find()
     *     {
     *         return parent::find()->limit(50);
     *     }
     * }
     * ```
     *
     * @return ElementQueryInterface The newly created [[ElementQueryInterface]] instance.
     */
    public static function find();

    /**
     * Returns a single element instance by a primary key or a set of element criteria parameters.
     *
     * The method accepts:
     *
     *  - an int: query by a single ID value and return the corresponding element (or null if not found).
     *  - an array of name-value pairs: query by a set of parameter values and return the first element
     *    matching all of them (or null if not found).
     *
     * Note that this method will automatically call the `one()` method and return an
     * [[ElementInterface|\craft\base\Element]] instance. For example,
     *
     * ```php
     * // find a single entry whose ID is 10
     * $entry = Entry::findOne(10);
     *
     * // the above code is equivalent to:
     * $entry = Entry::find->id(10)->one();
     *
     * // find the first user whose email ends in "example.com"
     * $user = User::findOne(['email' => '*example.com']);
     *
     * // the above code is equivalent to:
     * $user = User::find()->email('*example.com')->one();
     * ```
     *
     * @param mixed $criteria The element ID or a set of element criteria parameters
     *
     * @return static|null Element instance matching the condition, or null if nothing matches.
     */
    public static function findOne($criteria = null);

    /**
     * Returns a list of elements that match the specified ID(s) or a set of element criteria parameters.
     *
     * Note that this method will automatically call the `all()` method and return an array of
     * [[ElementInterface|\craft\base\Element]] instances. For example,
     *
     * ```php
     * // find the entries whose ID is 10
     * $entries = Entry::findAll(10);
     *
     * // the above code is equivalent to:
     * $entries = Entry::find()->id(10)->all();
     *
     * // find the entries whose ID is 10, 11 or 12.
     * $entries = Entry::findAll([10, 11, 12]);
     *
     * // the above code is equivalent to:
     * $entries = Entry::find()->id([10, 11, 12]])->all();
     *
     * // find users whose email ends in "example.com"
     * $users = User::findAll(['email' => '*example.com']);
     *
     * // the above code is equivalent to:
     * $users = User::find()->email('*example.com')->all();
     * ```
     *
     * @param mixed $criteria The element ID, an array of IDs, or a set of element criteria parameters
     *
     * @return static[] an array of Element instances, or an empty array if nothing matches.
     */
    public static function findAll($criteria = null): array;



    // Public Methods
    // =========================================================================

    /**
     * Returns the element’s ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Returns the field layout used by this element.
     *
     * @return FieldLayout|null
     */
    public function getFieldLayout();


    /**
     * Returns the element’s full URL.
     *
     * @return string|null
     */
    public function getUrl();

    /**
     * Returns an array of the element’s normalized custom field values, indexed by their handles.
     *
     * @param string[]|null $fieldHandles The list of field handles whose values need to be returned.
     *                                    Defaults to null, meaning all fields’ values will be returned.
     *                                    If it is an array, only the fields in the array will be returned.
     *
     * @return array The field values (handle => value)
     */
    public function getFieldValues(array $fieldHandles = null): array;


    /**
     * Sets the element’s custom field values.
     *
     * @param array $values The custom field values (handle => value)
     *
     * @return void
     */
    public function setFieldValues(array $values);

    /**
     * Returns the value for a given field.
     *
     * @param string $fieldHandle The field handle whose value needs to be returned
     *
     * @return mixed The field value
     */
    public function getFieldValue(string $fieldHandle);

    /**
     * Sets the value for a given field.
     *
     * @param string $fieldHandle The field handle whose value needs to be set
     * @param mixed  $value       The value to set on the field
     *
     * @return void
     */
    public function setFieldValue(string $fieldHandle, $value);

    /**
     * The url in the cp to edit the entry
     * @return string
     */
    public function getCpEditUrl(): string;

    /**
     * Returns the name of the table this element’s content is stored in.
     *
     * @return string
     */
    public function getContentTable(): string;

    /**
     * Returns the field column prefix this element’s content uses.
     *
     * @return string
     */
    public function getFieldColumnPrefix(): string;



    // Events
    // -------------------------------------------------------------------------

    /**
     * Performs actions before an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return bool Whether the element should be saved
     */
    public function beforeSave(bool $isNew): bool;

    /**
     * Performs actions after an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return void
     */
    public function afterSave(bool $isNew);

    /**
     * Performs actions before an element is deleted.
     *
     * @return bool Whether the element should be deleted
     */
    public function beforeDelete(): bool;

    /**
     * Performs actions after an element is deleted.
     *
     * @return void
     */
    public function afterDelete();
}
