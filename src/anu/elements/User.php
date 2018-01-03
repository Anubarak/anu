<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 25.12.2017
 * Time: 13:14
 */

namespace anu\elements;

use anu\base\Element;
use anu\db\ActiveQuery;
use anu\elements\db\ElementQuery;
use anu\elements\db\ElementQueryInterface;
use anu\elements\db\UserQuery;
use anu\records\UserRecord;

class User extends Element{

    public $username;
    public $firstName;
    public $lastName;
    public $email;
    public $password;
    public $newPassword;
    public $admin;
    public $client;
    public $photoId;

    public $oldPasswort;

    /**
     * @inheritdoc
     * @return UserQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find(): UserQuery
    {
        return new UserQuery(['type' => static::class]);
    }

    /**
     * @return string
     */
    public static function displayName(): string{
        return "users";
    }

    public static function hasTitles(): bool{
        return false;
    }

    public static function hasContent(): bool{
        return false;
    }

    public function afterSave(bool $isNew){
        if($isNew){
            $record =  new UserRecord();
            $record->id = $this->id;
        }else{
            $record = UserRecord::find()->where(['id' => $this->id])->one();
        }

        foreach ($record->getAttributes() as $k => $v) {
            $value = property_exists($this, $k) ? $this->$k : '';
            $record->setAttribute($k, $value);
        }
        if($this->password !== $this->oldPasswort){
            if($this->newPassword !== null){
                $record->password = password_hash($this->newPassword, PASSWORD_DEFAULT);
            }else{
                $record->password = password_hash($this->password, PASSWORD_DEFAULT);
            }
        }

        $record->save(false);

        parent::afterSave($isNew);
    }

    public function rules(){
        $rules = parent::rules();

        $rules[] = ['username', 'required'];
        $rules[] = ['email', 'required'];

        $rules[] = ['password', function ($attribute, $params, $validator){
            // user tries to change password...
            if($this->password !== $this->oldPasswort && $this->newPassword !== null){
                if(true || password_verify($this->password, $this->oldPasswort)){
                    return true;
                }else{
                    $this->addError('password', \Anu::t('anu', 'Passwords do not match'));
                }
            }
        }];

        return $rules;
    }

    public function init(){
        $this->oldPasswort = $this->password;
        parent::init();
    }
}