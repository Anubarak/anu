<?php

namespace anu\records;

use anu\db\ActiveRecord;
use anu\db\ActiveQueryInterface;

/**
 * Field layout record class.
 *
 * @property int                $id     ID
 * @property string             $type   Type
 * @property FieldLayoutTab[]   $tabs   Tabs
 * @property FieldLayoutField[] $fields Fields
 *
 * @author Robin Schamabch
 */
class FieldLayout extends ActiveRecord
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
        return '{{%fieldlayouts}}';
    }

    /**
     * Returns the field layout’s tabs.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getTabs(): ActiveQueryInterface
    {
        return $this->hasMany(FieldLayoutTab::class, ['layoutId' => 'id']);
    }

    /**
     * Returns the field layout’s fields.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getFields(): ActiveQueryInterface
    {
        return $this->hasMany(FieldLayoutField::class, ['layoutId' => 'id']);
    }
}
