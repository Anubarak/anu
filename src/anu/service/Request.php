<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 23.12.2017
 * Time: 17:14
 */

namespace anu\service;

use anu\base\ActionEvent;
use anu\base\Component;
use anu\base\InvalidRouteException;
use anu\base\Model;
use anu\db\Exception;
use anu\helper\LetterCase;
use anu\helper\Router;
use anu\helper\StringHelper;
use anu\helper\Url;

class Request extends Component{

    private $_params = [];

    private $_get = [];

    private $_post = [];

    public function __construct(){
        $this->_get = $_GET;
        $this->_post = $_POST;
        $this->_params = array_merge($this->_get, $this->_post);
        parent::__construct();
    }

    /**
     * @throws InvalidRouteException
     * @throws \anu\base\InvalidConfigException
     */
    public function handleRequest(){
        if($action = $this->getParam('action')){
            try{
                $parts = explode('/', $action);
                if(count($parts) == 2){
                    // only 2 parts => action leads to a custom controller with anu\controller
                    list($controller, $action) = $parts;
                    $controller = 'anu\\controller\\' . $controller;
                }else{
                    // TODO implement plugins route namespace
                }

                if (class_exists($controller)) {
                    // First check if is a static method, directly trying to invoke it.
                    // If isn't a valid static method, we will try as a normal method invocation.

                    $action = LetterCase::camel($action);

                    $action = 'action' . ucfirst($action);
                    $controller = \Anu::createObject($controller);
                    if ($response = call_user_func([$controller, $action])) {
                        if(StringHelper::isJson($response)){
                            echo $response;
                            die();
                        }
                    }
                }
                throw new InvalidRouteException();
            }catch(Exception $exception){
                throw new InvalidRouteException();
            }

        }

        $router = new Router();
        //$router->get('/(\d+)/(\w+)', '\anu\controller\Home@actionIndex', array(

        $router->get('/admin', 'anu\controller\admin@index');

        // fields
        //====================================================================
        $router->get('/admin/fields/new', '\anu\controller\Fields@render', array(
            'cp'    => true,
            'controller' => 'Fields',
            'fieldId'   => 0
        ));

        $router->get('/admin/fields/(\d+)', '\anu\controller\Fields@render', array(
            'fieldId'   => 0
        ));

        // sections
        //=====================================================================
        $router->get('/admin/sections/new', '\anu\controller\Sections@render', array(
            'cp'    => true,
            'controller' => 'Sections',
            'id'   => 0
        ));

        $router->get('/admin/sections/(\d+)', '\anu\controller\Sections@render', array(
            'id'   => 0
        ));

        // entryTypes
        //======================================================================
        $router->get('/admin/sections/(\d+)/entrytypes', '\anu\controller\Sections@render-entryTypes', array(
            'id'   => 0
        ));
        $router->get('/admin/sections/(\d+)/entrytypes/(\d+)', '\anu\controller\Sections@render-entryType', array(
            'id'   => 0,
            'entryTypeId' => 0
        ));

        $router->get('/<controller>(\w+)/<action>(\w+)', '\anu\controller\<controller>@<action>', array(
            'id'    => 4,
            'blub'  => 'foo'
        ));

        $router->get('', 'anu\controller\home@index');

        $resonses = $router->run();
        if($resonses){
            http_response_code(200);
        }
        foreach($resonses as $resonse){
            if(is_string($resonse)){
                echo $resonse;
            }
        }
    }

    /**
     * @param $values
     * @param $params
     * @return bool
     */
    public function setBodyParams($values, $params){
        $i = 0;
        foreach($params as $handle => $default){
            $this->_params[$handle] = ($values[$i])? $values[$i] : $default;
            $i++;
        }

        return true;
    }

    public function setBodyParam($handle, $value){
        $this->_params[$handle] = $value;
    }

    /**
     * @param $handle
     * @param null $default
     * @return mixed|null
     */
    public function getParam($handle = null, $default = null){
        if($handle === null){
            return $this->_params;
        }
        return isset($this->_params[$handle])? $this->_params[$handle] : $default;
    }


    /**
     * Check for valid ajax headers in jquery and angular
     * @see $_SERVER
     * @return bool
     */
    public function isAjaxRequest(){
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * Sends json
     *
     * @param array $var
     */
    public function returnJson($var = array())
    {
        echo json_encode($var);
        exit();
    }

    /**
     * Change header to redirect to a given url
     *
     * @param $to string|Model
     * @throws InvalidRouteException
     */
    public function redirect($to){
        $location = $to;
        if($to instanceof Model){
            $location = $to->getUrl();
        }

        if($location === null or empty($location)){
            throw new InvalidRouteException();
        }
        //$location = str_replace(BASE_URL, '', $location);
        //TODO oh god I know... this implementation is the worst I've ever made
        // but I got lazy and bored v.v

        header('Location: ' . $location);
        exit();
    }

    /**
     * return whether the current request is a cp request or not
     *
     * `http://anu.com/admin/dashboard` returns true
     * `https://anu.com/some/uri        returns false
     * @return bool
     */
    public function isCpRequest(){
        return strpos(Url::getCurrentUri(), 'admin') !== false;
    }
}