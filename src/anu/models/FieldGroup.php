<?php

namespace anu\models;

use Anu;
use anu\base\FieldInterface;
use anu\base\Model;
use anu\records\FieldGroup as FieldGroupRecord;
use anu\validators\UniqueValidator;

/**
 * FieldGroup model class.
 *
 * @author Robin Schambach
 */
class FieldGroup extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null ID
     */
    public $id;
    /**
     * @var string|null Name
     */
    public $name;
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id'], 'number', 'integerOnly' => true],
            [['name'], 'string', 'max' => 255],
            [['name'], UniqueValidator::class, 'targetClass' => FieldGroupRecord::class],
            [['name'], 'required'],
        ];
    }

    /**
     * Use the group name as the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string) $this->name;
    }

    /**
     * Returns the group's fields.
     *
     * @return FieldInterface[]
     * @throws \anu\base\InvalidConfigException
     */
    public function getFields(): array
    {
        return Anu::$app->getFields()->getFieldsByGroupId($this->id);
    }
}
