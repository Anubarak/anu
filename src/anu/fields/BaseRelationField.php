<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 30.12.2017
 * Time: 20:45
 */

namespace anu\fields;

use Anu;
use anu\base\ElementInterface;
use anu\base\ModelEvent;
use anu\base\NotSupportedException;
use anu\elements\db\ElementQueryInterface;
use anu\models\Field;
use anu\validators\ArrayValidator;

class BaseRelationField extends Field
{
    // Properties
    // =========================================================================

    /**
     * @var string|string[]|null The source keys that this field can relate elements from (used if [[allowMultipleSources]] is set to true)
     */
    public $sources = '*';
    /**
     * @var string|null The source key that this field can relate elements from (used if [[allowMultipleSources]] is set to false)
     */
    public $source;
    /**
     * @var int|null The site that this field should relate elements from
     */
    public $targetSiteId;
    /**
     * @var string|null The view mode
     */
    public $viewMode;
    /**
     * @var int|null The maximum number of relations this field can have (used if [[allowLimit]] is set to true)
     */
    public $limit;
    /**
     * @var string|null The label that should be used on the selection input
     */
    public $selectionLabel;
    /**
     * @var bool Whether to allow multiple source selection in the settings
     */
    public $allowMultipleSources = true;
    /**
     * @var bool Whether to allow the Limit setting
     */
    public $allowLimit = true;
    /**
     * @var bool Whether to allow the “Large Thumbnails” view mode
     */
    protected $allowLargeThumbsView = false;
    /**
     * @var string Temlpate to use for settings rendering
     */
    protected $settingsTemplate = '_components/fieldtypes/elementfieldsettings';
    /**
     * @var string Template to use for field rendering
     */
    protected $inputTemplate = '_includes/forms/elementSelect';
    /**
     * @var string|null The JS class that should be initialized for the input
     */
    protected $inputJsClass;
    /**
     * @var bool Whether the elements have a custom sort order
     */
    protected $sortable = true;
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function hasContentColumn(): bool
    {
        return false;
    }

    /**
     * Returns the default [[selectionLabel]] value.
     *
     * @return string The default selection label
     */
    public static function defaultSelectionLabel(): string
    {
        return Anu::t('anu', 'Choose');
    }

    /**
     * Returns the default field label when creating new fields
     *
     * @return string
     */
    public static function getFieldLabel(): string
    {
        return Anu::t('anu', 'Text');
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // Not possible to have no sources selected
        if (!$this->sources) {
            $this->sources = '*';
        }
    }

    /**
     * @inheritdoc
     */
    public function settingsAttributes(): array
    {
        $attributes = parent::settingsAttributes();
        $attributes[] = 'sources';
        $attributes[] = 'source';
        $attributes[] = 'viewMode';
        $attributes[] = 'limit';
        $attributes[] = 'selectionLabel';

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function beforeSave(bool $isNew): bool
    {
        // Trigger a 'beforeSave' event
        $event = new ModelEvent(
            [
                'isNew' => $isNew,
            ]
        );
        $this->trigger(self::EVENT_BEFORE_SAVE, $event);

        return $event->isValid;
    }

    // Events
    // -------------------------------------------------------------------------

    /**
     * @inheritdoc
     * @throws \anu\base\InvalidRouteException
     * @throws \anu\base\InvalidConfigException
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Loader
     */
    public function getSettingsHtml()
    {
        return Anu::$app->getTemplate()->render(
            $this->settingsTemplate,
            [
                'field' => $this,
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function getElementValidationRules(): array
    {
        return [
            [
                ArrayValidator::class,
                'max'     => $this->allowLimit && $this->limit ? $this->limit : null,
                'tooMany' => Anu::t('app', '{attribute} should contain at most {max, number} {max, plural, one{selection} other{selections}}.'),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function isEmpty($value): bool
    {
        /** @var \anu\elements\db\ElementQueryInterface $value */
        return $value->count() === 0;
    }

    /**
     * @param ElementInterface|null $element
     *
     * @inheritdoc
     * @throws \anu\base\NotSupportedException
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        if ($value instanceof ElementQueryInterface) {
            return $value;
        }

        /** @var \anu\base\Element|null $element */
        /** @var \anu\base\Element $class */
        $class = static::elementType();
        /** @var \anu\elements\db\ElementQuery $query */
        $query = $class::find();

        // $value will be an array of element IDs if there was a validation error
        if (\is_array($value)) {
            $query->id(array_values(array_filter($value)))->fixedOrder();
        } else {
            if ($value !== '' && $element && $element->id) {
                $query->innerJoin(
                    '{{%relations}} relations',
                    [
                        'and',
                        '[[relations.targetId]] = [[elements.id]]',
                        [
                            'relations.sourceId' => $element->id,
                            'relations.fieldId'  => $this->id,
                        ],
                        [
                            'or',
                            ['relations.sourceSiteId' => null],
                            ['relations.sourceSiteId' => $element->siteId]
                        ]
                    ]
                );

                if ($this->sortable) {
                    $query->orderBy(['relations.sortOrder' => SORT_ASC]);
                }

                if (!$this->allowMultipleSources && $this->source) {
                    $source = ElementHelper::findSource($class, $this->source);

                    // Does the source specify any criteria attributes?
                    if (isset($source['criteria'])) {
                        Craft::configure($query, $source['criteria']);
                    }
                }
            } else {
                $query->id(false);
            }
        }

        if ($this->allowLimit && $this->limit) {
            $query->limit($this->limit);
        }

        return $query;
    }

    /**
     * Returns the element class associated with this field type.
     *
     * @return string The Element class name
     * @throws \anu\base\NotSupportedException if the method hasn't been implemented by the subclass
     */
    protected static function elementType(): string
    {
        throw new NotSupportedException('"elementType()" is not implemented.');
    }
}