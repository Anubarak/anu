<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 25.12.2017
 * Time: 11:39
 */

namespace anu\records;

use anu\db\ActiveRecord;

/**
 * User record connects to {{%elements}} table
 *
 * @property int    $id            ID
 * @property int    $fieldLayoutId ID
 * @property string $type          Type
 * @property bool   $enabled       Enabled
 * @property bool   $archived      Archived
 *
 * @author Robin Schambach
 */
class ElementRecord extends ActiveRecord{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName(): string
    {
        return '{{%elements}}';
    }
}