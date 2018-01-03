<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 26.12.2017
 * Time: 12:33
 */

namespace anu\controller;
use Anu;
use anu\base\Controller;
use anu\base\InvalidParamException;
use anu\base\Model;
use anu\db\Query;
use anu\migrations\Install;
use anu\models\Field;
use http\Url;

/**
 * Usercontroller
 *
 * Class User
 * @package anu\controller
 */
class User extends Controller{

    public function actionIndex(){
    }

    public function actionLogin(){
        $user = $this->_getUserElement();
        $this->_populateUser($user);

        if($value = Anu::$app->getRequest()->getParam('username')){
            if(filter_var($value, FILTER_VALIDATE_EMAIL)){
                $user->email = $value;
            }else{
                $user->username = $value;
            }
        }

        $response = [
            'success'   => false,
            'message'   => Anu::t('app', 'An internal error occured please try again later')
        ];
        $redirect = Anu::$app->getRequest()->getParam('redirect');
        if(Anu::$app->getUser()->login($user)){
            //  user is logged in
            if($this->isAjaxRequest()){

                $response['success'] = true;
                $response['redirect'] = $redirect? \anu\helper\Url::to($redirect) : \anu\helper\Url::to('admin/dashboard');
                $response['user']   = $user->getAttributes();
                return $this->asJson($response);
            }

            return $this->redirect(\anu\helper\Url::to('admin/dashboard'));
        }
        $response['errors'] = $user->getErrors();

        return $this->asJson($response);
    }

    /**
     * Generates user By post Request
     *
     * @return \anu\base\ElementInterface|\anu\elements\User|array|null
     */
    private function _getUserElement(){
        $request = Anu::$app->getRequest();
        if($id = $request->getParam('id', null)){
            $user = \anu\elements\User::find()->id($id)->one();
            if($user === null){
                throw new InvalidParamException();
            }
        }else{
            $user = new \anu\elements\User();
        }

        return $user;
    }

    /**
     * Populates element by post request
     *
     * @param \anu\elements\User $user
     */
    private function _populateUser(\anu\elements\User $user){
        $request = Anu::$app->getRequest();
        $user->username     = $request->getParam('username', $user->username);
        $user->password     = $request->getParam('password', $user->password);
        $user->email        = $request->getParam('email', $user->email);
        $user->firstName    = $request->getParam('firstName', $user->firstName);
        $user->lastName     = $request->getParam('lastName', $user->lastName);
    }
}