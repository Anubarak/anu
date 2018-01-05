<?php

namespace anu\records;

use anu\db\ActiveRecord;
use anu\db\ActiveQueryInterface;
use anu\records\FieldLayoutTab;
use anu\records\FieldRecord as Field;

/**
 * Class FieldLayoutField record.
 *
 * @property int            $id        ID
 * @property int            $layoutId  Layout ID
 * @property int            $tabId     Tab ID
 * @property int            $fieldId   Field ID
 * @property bool           $required  Required
 * @property int            $sortOrder Sort order
 * @property FieldLayout    $layout    Layout
 * @property FieldLayoutTab $tab       Tab
 * @property Field          $field     Field
 *
 * @author Robin Schambach
 */
class FieldLayoutField extends ActiveRecord
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
        return '{{%fieldlayoutfields}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['layoutId'], 'unique', 'targetAttribute' => ['layoutId', 'fieldId']],
        ];
    }

    /**
     * Returns the field layout field’s layout.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getLayout(): ActiveQueryInterface
    {
        return $this->hasOne(FieldLayout::class, ['id' => 'layoutId']);
    }

    /**
     * Returns the field layout field’s tab.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getTab(): ActiveQueryInterface
    {
        return $this->hasOne(FieldLayoutTab::class, ['id' => 'tabId']);
    }

    /**
     * Returns the field layout field’s field.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getField(): ActiveQueryInterface
    {
        return $this->hasOne(Field::class, ['id' => 'fieldId']);
    }
}
