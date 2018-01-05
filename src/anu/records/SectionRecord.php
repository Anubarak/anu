<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 31.12.2017
 * Time: 21:39
 */

namespace anu\records;

use anu\db\ActiveRecord;

/**
 * Section record connects to {{%sections}} table
 * @property mixed $handle
 * @property mixed $name
 * @property mixed $type
 * @author Robin Schambach
 */
class SectionRecord extends ActiveRecord{
    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%sections}}';
    }
}