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
 * Field Group record connects to {{%fieldgroups}} table
 *
 * @author Robin Schambach
 */
class FieldGroupRecord extends ActiveRecord{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%fieldgroups}}';
    }
}