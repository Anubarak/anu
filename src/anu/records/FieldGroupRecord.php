<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 25.12.2017
 * Time: 11:39
 */

namespace anu\records;

use anu\db\ActiveRecord;
use anu\models\Field;

/**
 * Field Group record connects to {{%fieldgroups}} table
 *
 * @property int id
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

    /**
     * @return \anu\models\Field[]
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\di\InvalidConfigException
     * @throws \anu\di\NotInstantiableException
     */
    public function getFields()
    {
        return \Anu::$app->getFields()->getFieldsForGroup($this->id);
    }

    /**
     * Json serialize interface
     *
     * @return array
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\di\InvalidConfigException
     * @throws \anu\di\NotInstantiableException
     */
    public function jsonSerialize()
    {
        return array_merge(
            $this->getAttributes(),
            [
                'fields' => $this->getFields()
            ]
        );
    }
}