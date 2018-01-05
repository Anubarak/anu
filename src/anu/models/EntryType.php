<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 31.12.2017
 * Time: 19:10
 */

namespace anu\models;



use Anu;
use anu\base\SavableComponent;
use anu\behaviors\FieldLayoutBehavior;
use anu\elements\Entry;
use anu\helper\Url;
use anu\records\EntryTypeRecord;
use anu\validators\UniqueValidator;

/**
 * EntryType model class.
 *
 * @mixin FieldLayoutBehavior
 *
 * @author Robin Schambach
 */
class EntryType extends SavableComponent
{
    // Properties
    // =========================================================================

    /**
     * @var int|null ID
     */
    public $id;

    /**
     * @var int|null Section ID
     */
    public $sectionId;

    /**
     * @var int|null Field layout ID
     */
    public $fieldLayoutId;

    /**
     * @var string|null Name
     */
    public $name;

    /**
     * @var string|null Handle
     */
    public $handle;

    /**
     * @var bool Has title field
     */
    public $hasTitleField = true;

    /**
     * @var string Title label
     */
    public $titleLabel = 'Title';

    /**
     * @var string|null Title format
     */
    public $titleFormat;

    /**
     * @var int $sortOrder
     */
    public $sortOrder;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'sectionId', 'fieldLayoutId'], 'number', 'integerOnly' => true],
            [['name', 'handle'], 'required'],
            [['name', 'handle'], 'string', 'max' => 255],
            [
                ['name'],
                UniqueValidator::class,
                'targetClass' => EntryTypeRecord::class,
                'targetAttribute' => ['name', 'sectionId'],
                'comboNotUnique' => Anu::t('anu', '{attribute} "{value}" has already been taken.'),
            ],
            [
                ['handle'],
                UniqueValidator::class,
                'targetClass' => EntryTypeRecord::class,
                'targetAttribute' => ['handle', 'sectionId'],
                'comboNotUnique' => Anu::t('anu', '{attribute} "{value}" has already been taken.'),
            ],
            [
                ['titleLabel'],
                'required',
                'when' => function ($model, $attribute) {
                    /** @var static $model */
                    return $model->hasTitleField;
                }
            ],
            [
                ['titleFormat'],
                'required',
                'when' => function ($model, $attribute) {
                    /** @var static $model */
                    return !$model->hasTitleField;
                }
            ],
        ];
    }

    /**
     * Use the handle as the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->handle;
    }

    /**
     * Returns the entry’s CP edit URL.
     *
     * @return string
     */
    public function getCpEditUrl(): string
    {
        return Url::to('/admin/sections/'.$this->sectionId.'/entrytypes/'.$this->id);
    }

    /**
     * Returns the entry type’s section.
     *
     * @return Section|null
     * @throws \anu\base\InvalidConfigException
     */
    public function getSection()
    {
        if ($this->sectionId) {
            return Anu::$app->getSections()->getSectionById($this->sectionId);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'fieldLayout' => [
                'class'       => FieldLayoutBehavior::class,
                'elementType' => Entry::class
            ],
        ];
    }
}
