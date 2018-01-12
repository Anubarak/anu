<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 25.12.2017
 * Time: 20:45
 */

namespace anu\elements\db;

use anu\base\Element;
use anu\base\ElementInterface;
use anu\base\FieldInterface;
use anu\behaviors\ContentBehavior;
use anu\db\Query;
use anu\db\QueryAbortedException;
use anu\events\CancelableEvent;
use anu\events\PopulateElementEvent;
use anu\models\FieldLayout;

class ElementQuery extends Query implements ElementQueryInterface{

    // Constants
    // =========================================================================

    /**
     * @event Event An event that is triggered at the beginning of preparing an element query for the query builder.
     */
    public const EVENT_BEFORE_PREPARE = 'beforePrepare';

    /**
     * @event Event An event that is triggered at the end of preparing an element query for the query builder.
     */
    public const EVENT_AFTER_PREPARE = 'afterPrepare';

    /**
     * @event PopulateElementEvent The event that is triggered after an element is populated.
     */
    public const EVENT_AFTER_POPULATE_ELEMENT = 'afterPopulateElement';


    // Properties
    // =========================================================================

    public $dateCreated;
    public $dateUpdated;
    public $uid;
    /**
     * @var bool Whether results should be returned in the order specified by [[id]].
     */
    public $fixedOrder = false;
    public $asArray = false;
    private $customFields;

    /**
     * @var string|null The name of the [[ElementInterface]] class.
     */
    public $type;

    /**
     * @var Query|null The query object created by [[prepare()]]
     * @see prepare()
     */
    public $query;

    /**
     * @var Query|null The subselect’s query object created by [[prepare()]]
     * @see prepare()
     */
    public $subQuery;

    /**
     * @var string|null The content table that will be joined by this query.
     */
    public $contentTable = '{{%content}}';

    /**
     * The id of the element
     *
     * @var $id integer
     */
    public $id;

    /**
     * @inheritdoc
     */
    public function id($value)
    {
        $this->id = $value;

        return $this;
    }

    /**
     * @param \anu\db\QueryBuilder $builder
     *
     * @return Query
     * @throws QueryAbortedException
     * @throws \anu\base\InvalidConfigException
     */
    public function prepare($builder): Query
    {

        // Is the query already doomed?
        if ($this->id !== null && empty($this->id)) {
            throw new QueryAbortedException('Query has no valid id');
        }

        /** @var Element $class */
        $class = $this->type;

        // Build the query
        // ---------------------------------------------------------------------

        $this->query = new Query();
        $this->subQuery = new Query();

        // Give other classes a chance to make changes up front
        if (!$this->beforePrepare()) {
            throw new QueryAbortedException('Query was aborted before Prepare');
        }


        $this->query
            ->from(['subquery' => $this->subQuery])
            ->innerJoin('{{%elements}} elements', '[[elements.id]] = [[subquery.elementsId]]');

        $this->subQuery
            ->addSelect([
                            'elementsId' => 'elements.id',
                            'type'       => 'elements.type',
            ])
            ->from(['elements' => '{{%elements}}'])
            ->offset($this->offset)
            ->limit($this->limit)
            ->addParams($this->params);

        if ($class::hasContent() && $this->contentTable !== null) {
            $this->customFields = $this->customFields();
            $this->_joinContentTable($class);
        } else {
            $this->customFields = null;
        }


        if($this->where){
            $this->subQuery->andWhere($this->where);
        }

        if ($this->id) {
            $this->subQuery->andWhere(['elements.id' => $this->id]);
        }

        if ($this->uid) {
            $this->subQuery->andWhere(['elements.uid' => $this->uid]);
        }

        // Give other classes a chance to make changes up front
        if (!$this->afterPrepare()) {
            throw new QueryAbortedException("Aborted after prepare");
        }


        // Pass the query back
        return $this->query;

    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'customFields' => ContentBehavior::class,
        ];
    }

    protected function beforePrepare(): bool
    {
        $event = new CancelableEvent();
        $this->trigger(self::EVENT_BEFORE_PREPARE, $event);

        return $event->isValid;
    }

    protected function afterPrepare(): bool
    {
        $event = new CancelableEvent();
        $this->trigger(self::EVENT_AFTER_PREPARE, $event);

        return $event->isValid;
    }

    /**
     * Joins in a table with an `id` column that has a foreign key pointing to `elements`.`id`.
     *
     * @param string $table The unprefixed table name. This will also be used as the table’s alias within the query.
     */
    protected function joinElementTable(string $table)
    {
        $joinTable = "{{%{$table}}} {$table}";
        $this->query->innerJoin($joinTable, "[[{$table}.id]] = [[subquery.elementsId]]");
        $this->subQuery->innerJoin($joinTable, "[[{$table}.id]] = [[elements.id]]");
    }

    /**
     * @inheritdoc
     *
     * @return ElementInterface[]|array The resulting elements.
     */
    public function populate($rows)
    {
        if (empty($rows)) {
            return [];
        }

        return $this->_createElements($rows);
    }

    /**
     * @inheritdoc
     * @return ElementInterface|array|null the first element. Null is returned if the query
     * results in nothing.
     */
    public function one($db = null)
    {
        $row = parent::one($db);
        return $row ? $this->_createElement($row) : null;
    }


    // static functions
    // ==============================================================


    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string{
        // TODO: Implement displayName() method.
    }

    /**
     * Returns whether elements of this type will be storing any data in the `content` table (tiles or custom fields).
     *
     * @return bool Whether elements of this type will be storing any data in the `content` table.
     */
    public static function hasContent(): bool{
        // TODO: Implement hasContent() method.
    }

    /**
     * Returns whether elements of this type have traditional titles.
     *
     * @return bool Whether elements of this type have traditional titles.
     */
    public static function hasTitles(): bool{
        // TODO: Implement hasTitles() method.
    }

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
    public static function find(){
        // TODO: Implement find() method.
    }

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
    public static function findOne($criteria = null){
        // TODO: Implement findOne() method.
    }

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
    public static function findAll($criteria = null): array{
        // TODO: Implement findAll() method.
    }

    /**
     * Returns the element’s ID.
     *
     * @return int|null
     */
    public function getId(){
        // TODO: Implement getId() method.
    }

    /**
     * Returns the field layout used by this element.
     *
     * @return FieldLayout|null
     */
    public function getFieldLayout(){
        // TODO: Implement getFieldLayout() method.
    }

    /**
     * Returns the element’s full URL.
     *
     * @return string|null
     */
    public function getUrl(){
        // TODO: Implement getUrl() method.
    }

    /**
     * Returns an array of the element’s normalized custom field values, indexed by their handles.
     *
     * @param string[]|null $fieldHandles The list of field handles whose values need to be returned.
     *                                    Defaults to null, meaning all fields’ values will be returned.
     *                                    If it is an array, only the fields in the array will be returned.
     *
     * @return array The field values (handle => value)
     */
    public function getFieldValues(array $fieldHandles = null): array{
        // TODO: Implement getFieldValues() method.
    }

    /**
     * Sets the element’s custom field values.
     *
     * @param array $values The custom field values (handle => value)
     *
     * @return void
     */
    public function setFieldValues(array $values){
        // TODO: Implement setFieldValues() method.
    }

    /**
     * Returns the value for a given field.
     *
     * @param string $fieldHandle The field handle whose value needs to be returned
     *
     * @return mixed The field value
     */
    public function getFieldValue(string $fieldHandle){
        // TODO: Implement getFieldValue() method.
    }

    /**
     * Sets the value for a given field.
     *
     * @param string $fieldHandle The field handle whose value needs to be set
     * @param mixed $value The value to set on the field
     *
     * @return void
     */
    public function setFieldValue(string $fieldHandle, $value){
        // TODO: Implement setFieldValue() method.
    }

    /**
     * Returns the name of the table this element’s content is stored in.
     *
     * @return string
     */
    public function getContentTable(): string{
        // TODO: Implement getContentTable() method.
    }

    /**
     * Returns the field column prefix this element’s content uses.
     *
     * @return string
     */
    public function getFieldColumnPrefix(): string{
        // TODO: Implement getFieldColumnPrefix() method.
    }

    /**
     * Performs actions before an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return bool Whether the element should be saved
     */
    public function beforeSave(bool $isNew): bool{
        // TODO: Implement beforeSave() method.
    }

    /**
     * Performs actions after an element is saved.
     *
     * @param bool $isNew Whether the element is brand new
     *
     * @return void
     */
    public function afterSave(bool $isNew){
        // TODO: Implement afterSave() method.
    }

    /**
     * Performs actions before an element is deleted.
     *
     * @return bool Whether the element should be deleted
     */
    public function beforeDelete(): bool{
        // TODO: Implement beforeDelete() method.
    }

    /**
     * Performs actions after an element is deleted.
     *
     * @return void
     */
    public function afterDelete(){
        // TODO: Implement afterDelete() method.
    }

    // private functions
    //==================================================================

    /**
     * Converts a found row into an element instance.
     *
     * @param array $row
     *
     * @return ElementInterface
     */
    private function _createElement(array $row): ElementInterface
    {

        /** @var Element $class */
        $class = $this->type;

        if ($class::hasContent() && $this->contentTable !== null) {
            // Separate the content values from the main element attributes
            $fieldValues = [];

            if (!empty($this->customFields)) {
                foreach ($this->customFields as $field) {
                    /** @var Field $field */
                    if ($field->hasContentColumn()) {
                        // Account for results where multiple fields have the same handle, but from
                        // different columns e.g. two Matrix block types that each have a field with the
                        // same handle
                        $colName = $this->_getFieldContentColumnName($field);

                        if (!isset($fieldValues[$field->handle]) || (empty($fieldValues[$field->handle]) && !empty($row[$colName]))) {
                            $fieldValues[$field->handle] = $row[$colName] ?? null;
                        }

                        unset($row[$colName]);
                    }
                }
            }
        }

        /** @var Element $element */
        $element = new $class($row);

        // Set the custom field values
        /** @noinspection UnSafeIsSetOverArrayInspection - FP */
        if (isset($fieldValues)) {
            $element->setFieldValues($fieldValues);
        }

        //Fire an 'afterPopulateElement' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_POPULATE_ELEMENT)) {
            $this->trigger(self::EVENT_AFTER_POPULATE_ELEMENT, new PopulateElementEvent([
                'element' => $element,
                'row' => $row
            ]));
        }

        return $element;
    }

    /**
     * @return array
     * @throws \anu\base\InvalidConfigException
     */
    protected function customFields(): array
    {
        $contentService = \Anu::$app->getContent();
        $originalFieldContext = $contentService->fieldContext;
        $contentService->fieldContext = 'global';
        $fields = \Anu::$app->getFields()->getAllFields();
        $contentService->fieldContext = $originalFieldContext;

        return $fields;
    }

    /**
     * Joins the content table into the query being prepared.
     *
     * @param string $class
     *
     * @throws QueryAbortedException
     * @throws \anu\base\InvalidConfigException
     */
    private function _joinContentTable(string $class)
    {
        // Join in the content table on both queries
        $this->subQuery->innerJoin($this->contentTable . ' content', '[[content.elementId]] = [[elements.id]]');
        $this->subQuery->addSelect(['contentId' => 'content.id']);

        $this->query->innerJoin($this->contentTable . ' content', '[[content.id]] = [[subquery.contentId]]');

        // Select the content table columns on the main query
        $this->query->addSelect(['contentId' => 'content.id']);

        if ($class::hasTitles()) {
            $this->query->addSelect(['content.title']);
        }

        if (\is_array($this->customFields)) {
            $contentService = \Anu::$app->getContent();
            $originalFieldColumnPrefix = $contentService->fieldColumnPrefix;
            $fieldAttributes = $this->getBehavior('customFields');

            foreach ($this->customFields as $field) {
                /** @var \anu\models\Field $field */
                if ($field::hasContentColumn()) {
                    $this->query->addSelect(['content.' . $this->_getFieldContentColumnName($field)]);
                }

                $handle = $field->handle;

                // In theory all field handles will be accounted for on the ElementQueryBehavior, but just to be safe...
                if (isset($fieldAttributes->$handle)) {
                    $fieldAttributeValue = $fieldAttributes->$handle;
                } else {
                    $fieldAttributeValue = null;
                }

                // Set the field's column prefix on the Content service.
                if ($field->columnPrefix !== null) {
                    $contentService->fieldColumnPrefix = $field->columnPrefix;
                }

                $fieldResponse = $field->modifyElementsQuery($this, $fieldAttributeValue);

                // Set it back
                $contentService->fieldColumnPrefix = $originalFieldColumnPrefix;

                // Need to bail early?
                if ($fieldResponse === false) {
                    throw new QueryAbortedException('Field does not response');
                }
            }
        }
    }

    /**
     * Returns a field’s corresponding content column name.
     *
     * @param FieldInterface $field
     *
     * @return string
     */
    private function _getFieldContentColumnName(FieldInterface $field): string
    {
        /** @var \anu\models\Field $field */
        return ($field->columnPrefix ?: 'field_') . $field->handle;
    }

    /**
     * Converts found rows into element instances
     *
     * @param array $rows
     *
     * @return array|Element[]
     */
    private function _createElements(array $rows)
    {
        $elements = [];

        if ($this->asArray === true) {
            if ($this->indexBy === null) {
                return $rows;
            }

            foreach ($rows as $row) {
                if (\is_string($this->indexBy)) {
                    $key = $row[$this->indexBy];
                } else {
                    $key = \call_user_func($this->indexBy, $row);
                }

                $elements[$key] = $row;
            }
        } else {
            foreach ($rows as $row) {
                $element = $this->_createElement($row);

                // Add it to the elements array
                if ($this->indexBy === null) {
                    $elements[] = $element;
                } else {
                    if (\is_string($this->indexBy)) {
                        $key = $element->{$this->indexBy};
                    } else {
                        $key = \call_user_func($this->indexBy, $element);
                    }

                    $elements[$key] = $element;
                }
            }

            //ElementHelper::setNextPrevOnElements($elements);

            // Should we eager-load some elements onto these?
        }

        return $elements;
    }
}