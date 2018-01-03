<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 31.12.2017
 * Time: 19:15
 */

namespace anu\records;

use anu\db\ActiveQueryInterface;
use anu\db\ActiveRecord;
use anu\models\Section;

/**
 * Class EntryType record.
 *
 * @property int         $id            ID
 * @property int         $sectionId     Section ID
 * @property int         $fieldLayoutId Field layout ID
 * @property string      $name          Name
 * @property string      $handle        Handle
 * @property bool        $hasTitleField Has title field
 * @property string      $titleLabel    Title label
 * @property string      $titleFormat   Title format
 * @property int         $sortOrder     Sort order
 * @property Section     $section       Section
 *
 * @author Robin Schambach
 */
class EntryTypeRecord extends ActiveRecord
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
        return '{{%entrytypes}}';
    }

    /**
     * Returns the entry type’s section.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getSection(): ActiveQueryInterface
    {
        return $this->hasOne(Section::class, ['id' => 'sectionId']);
    }

    /**
     * Returns the entry type’s fieldLayout.
     *
     * @return ActiveQueryInterface The relational query object.
     */
    public function getFieldLayout(): ActiveQueryInterface
    {
        return $this->hasOne(FieldLayout::class, ['id' => 'fieldLayoutId']);
    }
}
