<?php
/**
 * @link      https://craftcms.com/
 * @copyright Copyright (c) Pixel & Tonic, Inc.
 * @license   https://craftcms.github.io/license/
 */

namespace anu\records;

use anu\db\ActiveRecord;
use anu\db\ActiveQueryInterface;
use anu\records\FieldLayout;
use anu\records\FieldLayoutField;

/**
 * Field record class.
 *
 * @property int                $id        ID
 * @property int                $layoutId  Layout ID
 * @property string             $name      Name
 * @property int                $sortOrder Sort order
 * @property FieldLayout        $layout    Layout
 * @property FieldLayoutField[] $fields    Fields
 *
 * @author Robin Schambach
 */
class FieldLayoutTab extends ActiveRecord
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
        return '{{%fieldlayouttabs}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
        ];
    }

    /**
     * Returns the field layout tab’s layout.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getLayout(): ActiveQueryInterface
    {
        return $this->hasOne(FieldLayout::class, ['id' => 'layoutId']);
    }

    /**
     * Returns the field layout tab’s fields.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getFields(): ActiveQueryInterface
    {
        return $this->hasMany(FieldLayoutField::class, ['tabId' => 'id']);
    }
}
