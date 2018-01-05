<?php

namespace anu\records;

use anu\db\ActiveRecord;
use anu\db\ActiveQueryInterface;
use anu\records\FieldRecord as Field;

/**
 * Class FieldGroup record.
 *
 * @property int     $id     ID
 * @property string  $name   Name
 * @property Field[] $fields Fields
 *
 * @author Robin Schambach
 */
class FieldGroup extends ActiveRecord
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     *
     * @return string
     */
    public static function tableName(): string
    {
        return '{{%fieldgroups}}';
    }

    /**
     * Returns the field group’s fields.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getFields(): ActiveQueryInterface
    {
        return $this->hasMany(Field::class, ['groupId' => 'id']);
    }
}
