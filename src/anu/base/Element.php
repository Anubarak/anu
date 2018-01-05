<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.github.io/license/
 */

namespace anu\base;

use Anu;
use anu\db\Query;
use anu\elements\db\ElementQuery;
use anu\elements\db\ElementQueryInterface;
use anu\helper\ArrayHelper;
use anu\helper\UploadFileHelper;
use anu\models\Field;
use anu\validators\UniqueValidator;

abstract class Element extends Model implements ElementInterface
{

    // Traits
    // =========================================================================

    use ElementTrait;

    // Constants
    // =========================================================================

    // Statuses
    // -------------------------------------------------------------------------

    public const STATUS_ENABLED = 'enabled';
    public const STATUS_DISABLED = 'disabled';

    // Validation scenarios
    // -------------------------------------------------------------------------

    public const SCENARIO_ESSENTIALS = 'essentials';
    public const SCENARIO_LIVE = 'live';

    // Events
    // -------------------------------------------------------------------------


    /**
     * @event ModelEvent The event that is triggered before the element is saved
     *
     * You may set [[ModelEvent::isValid]] to `false` to prevent the element from getting saved.
     */
    public const EVENT_BEFORE_SAVE = 'beforeSave';

    /**
     * @event ModelEvent The event that is triggered after the element is saved
     */
    public const EVENT_AFTER_SAVE = 'afterSave';

    /**
     * @event ModelEvent The event that is triggered before the element is deleted
     *
     * You may set [[ModelEvent::isValid]] to `false` to prevent the element from getting deleted.
     */
    public const EVENT_BEFORE_DELETE = 'beforeDelete';

    /**
     * @event \yii\base\Event The event that is triggered after the element is deleted
     */
    public const EVENT_AFTER_DELETE = 'afterDelete';
    // Private
    // =========================================================================
    /**
     * @var string $_fieldParamNamePrefix the field handle name
     */
    private $_fieldParamNamePrefix;
    /**
     * @var
     */
    private $_fieldsByHandle;
    /**
     * @var array|null Record of the fields whose values have already been normalized
     */
    private $_normalizedFieldValues;


    // Static
    // =========================================================================


    /**
     * @inheritdoc
     */
    public static function hasContent(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public static function hasTitles(): bool
    {
        return false;
    }


    /**
     * @inheritdoc
     *
     * @return
     */
    public static function find()
    {
        return new ElementQuery(['type' => static::class]);
    }

    /**
     * @inheritdoc
     */
    public static function findOne($criteria = null)
    {
        return static::findByCondition($criteria, true);
    }

    /**
     * @inheritdoc
     */
    public static function findAll($criteria = null): array
    {
        return static::findByCondition($criteria, false);
    }






    // Public Methods
    // =========================================================================

    /**
     * Returns the string representation of the element.
     *
     * @return string
     */
    public function __toString()
    {
        if ($this->title) {
            return (string)$this->title;
        }
        return (string)$this->id ?: static::class;
    }

    /**
     * Checks if a property is set.
     *
     * This method will check if $name is one of the following:
     *
     * - "title"
     * - a magic property supported by [[\anu\base\Component::__isset()]]
     * - a custom field handle
     *
     * @param string $name The property name
     *
     * @return bool Whether the property is set
     * @throws \anu\base\InvalidConfigException
     */
    public function __isset($name): bool
    {
        return $name === 'title' || parent::__isset($name) || $this->fieldByHandle($name);
    }

    /**
     * Returns a property value.
     *
     * This method will check if $name is one of the following:
     *
     * - a magic property supported by [[\yii\base\Component::__isset()]]
     * - a custom field handle
     *
     * @param string $name The property name
     *
     * @return mixed The property value
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\Exception
     * @throws UnknownPropertyException if the property is not defined
     * @throws InvalidCallException if the property is write-only.
     */
    public function __get($name)
    {
        // Give custom fields priority over other getters so we have a chance to prepare their values
        $field = $this->fieldByHandle($name);
        if ($field !== null) {
            return $this->getFieldValue($name);
        }

        return parent::__get($name);
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            //'customFields' => ContentBehavior::class,
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     */
    public function attributes()
    {
        $names = parent::attributes();

        // Include custom field handles
        if (static::hasContent() && ($fieldLayout = $this->getFieldLayout()) !== null) {
            foreach ($fieldLayout->getFields() as $field) {
                /** @var Field $field */
                $names[] = $field->handle;
            }
        }

        // In case there are any field handles that had the same name as an existing property
        return array_unique($names);
    }

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     */
    public function attributeLabels()
    {
        $labels = [
            'id' => Anu::t('app', 'ID'),
            'slug' => Anu::t('app', 'Slug'),
            'title' => Anu::t('app', 'Title'),
            'uid' => Anu::t('app', 'UID'),
            'uri' => Anu::t('app', 'URI'),
        ];

        $layout = $this->getFieldLayout();

        if ($layout !== null) {
            foreach ($layout->getFields() as $field) {
                /** @var Field $field */
                $labels[$field->handle] = Anu::t('site', $field->name);
            }
        }

        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['id', 'contentId'], 'number', 'integerOnly' => true, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_LIVE]],
        ];

        if (static::hasTitles()) {
            $rules[] = [['title'], 'string', 'max' => 255, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_LIVE]];
            $rules[] = [['title'], 'required', 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_LIVE]];
        }
        //$rules[] = ['handle', UniqueValidator::class, 'targetClass' => \anu\records\ElementRecord::class];
        //$rules[] = [['slug'], 'string', 'max' => 255, 'on' => [self::SCENARIO_DEFAULT, self::SCENARIO_LIVE, self::SCENARIO_ESSENTIALS]];

        return $rules;
    }

    /**
     * Calls a custom validation function on a custom field.
     *
     * This will be called by [[\yii\validators\InlineValidator]] if a custom field specified
     * a closure or the name of a class-level method as the validation type.
     *
     * @param string     $attribute The field handle
     * @param array|null $params
     *
     * @return void
     */
    public function validateCustomFieldAttribute(string $attribute, array $params = null): void
    {
        /** @var Field $field */
        /** @var array|null $params */
        list($field, $method, $fieldParams) = $params;

        if (is_string($method)) {
            $method = [$field, $method];
        }

        $method($this, $fieldParams);
    }


    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     */
    public function getFieldLayout()
    {
        if ($this->fieldLayoutId) {
            return Anu::$app->getFields()->getLayoutById($this->fieldLayoutId);
        }

        return null;
    }


    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        if ($this->uri !== null) {
            $path = ($this->uri === '__home__') ? '' : $this->uri;

            return UrlHelper::siteUrl($path, null, null, $this->siteId);
        }

        return null;
    }

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\Exception
     */
    public function getFieldValues(array $fieldHandles = null): array
    {
        $values = [];

        foreach ($this->fieldLayoutFields() as $field) {
            if ($fieldHandles === null || in_array($field->handle, $fieldHandles, true)) {
                $values[$field->handle] = $this->getFieldValue($field->handle);
            }
        }

        return $values;
    }


    /**
     * @inheritdoc
     */
    public function setFieldValues(array $values)
    {
        foreach ($values as $fieldHandle => $value) {
            $this->setFieldValue($fieldHandle, $value);
        }
    }

    /**
     * @inheritdoc
     * @throws \anu\base\Exception
     */
    public function getFieldValue(string $fieldHandle)
    {
        // Is this the first time this field value has been accessed?
        if (!isset($this->_normalizedFieldValues[$fieldHandle])) {
            $this->normalizeFieldValue($fieldHandle);
        }

        return $this->getBehavior('customFields')->$fieldHandle;
    }

    /**
     * @inheritdoc
     */
    public function setFieldValue(string $fieldHandle, $value)
    {
        $behavior = $this->getBehavior('customFields');
        $behavior->$fieldHandle = $value;

        // Don't assume that $value has been normalized
        unset($this->_normalizedFieldValues[$fieldHandle]);
    }

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     */
    public function getContentTable(): string
    {
        return Anu::$app->getContent()->contentTable;
    }

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     */
    public function getFieldColumnPrefix(): string
    {
        return Anu::$app->getContent()->fieldColumnPrefix;
    }

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     */
    public function getFieldContext(): string
    {
        return Anu::$app->getContent()->fieldContext;
    }


    // Events
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     */
    public function beforeSave(bool $isNew): bool
    {
        // Tell the fields about it
        foreach ($this->fieldLayoutFields() as $field) {
            if (!$field->beforeElementSave($this, $isNew)) {
                return false;
            }
        }

        // Trigger a 'beforeSave' event
        $event = new ModelEvent([
            'isNew' => $isNew,
        ]);
        $this->trigger(self::EVENT_BEFORE_SAVE, $event);

        return $event->isValid;
    }

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     */
    public function afterSave(bool $isNew)
    {
        // Tell the fields about it
        foreach ($this->fieldLayoutFields() as $field) {
            $field->afterElementSave($this, $isNew);
        }

        // Trigger an 'afterSave' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE)) {
            $this->trigger(self::EVENT_AFTER_SAVE, new ModelEvent([
                'isNew' => $isNew,
            ]));
        }
    }

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     */
    public function beforeDelete(): bool
    {
        // Tell the fields about it
        foreach ($this->fieldLayoutFields() as $field) {
            if (!$field->beforeElementDelete($this)) {
                return false;
            }
        }

        // Trigger a 'beforeDelete' event
        $event = new ModelEvent();
        $this->trigger(self::EVENT_BEFORE_DELETE, $event);

        return $event->isValid;
    }

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     */
    public function afterDelete()
    {
        // Tell the fields about it
        foreach ($this->fieldLayoutFields() as $field) {
            $field->afterElementDelete($this);
        }

        // Trigger an 'afterDelete' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_DELETE)) {
            $this->trigger(self::EVENT_AFTER_DELETE);
        }
    }

    /**
     * @inheritdoc
     */
    public function setFieldParamNamespace(string $namespace)
    {
        $this->_fieldParamNamePrefix = $namespace;
    }

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidConfigException
     */
    public function setFieldValuesFromRequest(string $paramNamespace = '')
    {
        $this->setFieldParamNamespace($paramNamespace);
        $values = Anu::$app->getRequest()->getParam($paramNamespace, []);

        foreach ($this->fieldLayoutFields() as $field) {
            // Do we have any post data for this field?
            if (isset($values[$field->handle])) {
                $value = $values[$field->handle];
                //TODO file upload here....
            } else {
                if (!empty($this->_fieldParamNamePrefix) && UploadFileHelper::getUploadedFile($field->handle)) {
                    // A file was uploaded for this field
                    $value = null;
                } else {
                    continue;
                }
            }

            $this->setFieldValue($field->handle, $value);
        }
    }


    // Protected Methods
    // =========================================================================

    /**
     * Normalizes a field’s value.
     *
     * @param string $fieldHandle The field handle
     *
     * @return void
     * @throws Exception if there is no field with the handle $fieldValue
     */
    protected function normalizeFieldValue(string $fieldHandle)
    {
        $field = $this->fieldByHandle($fieldHandle);

        if (!$field) {
            throw new Exception('Invalid field handle: '.$fieldHandle);
        }

        $behavior = $this->getBehavior('customFields');
        $behavior->$fieldHandle = $field->normalizeValue($behavior->$fieldHandle, $this);
        $this->_normalizedFieldValues[$fieldHandle] = true;
    }

    /**
     * Finds Element instance(s) by the given condition.
     *
     * This method is internally called by [[findOne()]] and [[findAll()]].
     *
     * @param mixed $criteria Refer to [[findOne()]] and [[findAll()]] for the explanation of this parameter
     * @param bool  $one      Whether this method is called by [[findOne()]] or [[findAll()]]
     *
     * @return static|static[]|null
     */
    protected static function findByCondition($criteria, bool $one)
    {
        /** @var ElementQueryInterface $query */
        $query = static::find();

        if ($criteria !== null) {
            if (!ArrayHelper::isAssociative($criteria)) {
                $criteria = ['id' => $criteria];
            }
            //Craft::configure($query, $criteria);
        }

        if ($one) {
            /** @var Element|null $result */
            $result = $query->one();
        } else {
            /** @var Element[] $result */
            $result = $query->all();
        }

        return $result;
    }

    /**
     * Returns each of this element’s fields.
     *
     * @return Field[] This element’s fields
     * @throws \anu\base\InvalidConfigException
     */
    protected function fieldLayoutFields(): array
    {
        $fieldLayout = $this->getFieldLayout();

        if ($fieldLayout) {
            return $fieldLayout->getFields();
        }

        return [];
    }

    /**
     * Returns the field with a given handle.
     *
     * @param string $handle
     *
     * @return Field|null
     * @throws \anu\base\InvalidConfigException
     */
    protected function fieldByHandle(string $handle)
    {
        return null;

        if ($this->_fieldsByHandle !== null && array_key_exists($handle, $this->_fieldsByHandle)) {
            return $this->_fieldsByHandle[$handle];
        }

        $contentService = Craft::$app->getContent();
        $originalFieldContext = $contentService->fieldContext;
        $contentService->fieldContext = $this->getFieldContext();
        $fieldLayout = $this->getFieldLayout();
        $this->_fieldsByHandle[$handle] = $fieldLayout ? $fieldLayout->getFieldByHandle($handle) : null;
        $contentService->fieldContext = $originalFieldContext;

        return $this->_fieldsByHandle[$handle];
    }

}
