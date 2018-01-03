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
 * Field record connects to {{%field}} table
 * @property int        $id                   ID
 * @property int        $groupId              Group ID
 * @property string     $name                 Name
 * @property string     $handle               Handle
 * @property string     $instructions         Instructions
 * @property string     $type                 Type
 * @property array      $settings             Settings
 * @property FieldGroupRecord $group                Group
 *
 * @author Robin Schambach
 */
class FieldRecord extends ActiveRecord{

    public $oldHandle;

    public function afterFind(){
       if($this->handle !== 'null'){
           $this->oldHandle = $this->handle;
       }
       if($this->settings){
           $this->settings = json_decode($this->settings);
       }
    }

    /**
     * @return mixed
     */
    public function getOldHandle(){
        return $this->oldHandle;
    }

    /**
     * @return string the name of the table associated with this ActiveRecord class.
     */
    public static function tableName()
    {
        return '{{%fields}}';
    }
}