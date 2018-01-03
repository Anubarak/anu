<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 25.12.2017
 * Time: 15:17
 */

namespace anu\service;
use Anu;
use anu\base\Component;
use anu\records\UserRecord;

class User extends Component{

    public function init(){
        $this->currentUser = Anu::$app->getSession()->get('currentUser');
    }

    /**
     * @var \anu\elements\User $currentUser
     */
    private $currentUser = null;

    /**
     * @param $email
     * @param $password
     */
    public function login(\anu\elements\User $user){
        if(!$user->username && !$user->email){
            $user->addError('username', Anu::t('anu', 'Please insert a username or password'));
        }
        if(!$user->password === null){
            $user->addError('password', Anu::t('anu', 'Please insert a password'));
        }

        $userElement = \anu\elements\User::find()->where([
            'OR',
                ['username'    => $user->username],
                [ 'email'    => $user->email]
        ])->one();


        if($userElement === null){
            $user->addError('username', Anu::parse('No user Found with email or username "{email}"}', array(
                'email' => $user->email
            )));
            return false;
        }

        if($user->password === null){
            $user->addError('password', Anu::t('anu', 'Please fill out your password'));
            return false;
        }


        if (password_verify($user->password, $userElement->password)) {
            $this->currentUser = $userElement;
            $user->setAttributes($userElement->getAttributes(), false);
            Anu::$app->getSession()->set('currentUser', $user);
            Anu::$app->getSession()->set('userId', $user->id);
            return true;
        }
        $user->addError('password', Anu::t('anu', 'Password do not match'));
        return false;
    }

    /**
     * Returns the current user or null if there is none
     *
     * @return \anu\elements\User|null
     */
    public function currentUser(){
        return $this->currentUser;
    }
}