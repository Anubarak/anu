<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 23.12.2017
 * Time: 17:13
 */
namespace anu\web;
use anu\db\Connection;
use anu\di\ServiceLocator;
use anu\helper\lettercase\LetterCase;
use anu\service\Config;
use anu\service\Content;
use anu\service\Db;
use anu\service\Elements;
use anu\service\Fields;
use anu\service\Request;
use anu\service\Sections;
use anu\service\Session;
use anu\service\Template;
use anu\service\User;

/**
 * Class Application
 * @package anu\web
 *
 * @property Request        $request
 * @property Config         $config
 * @property Template       $template
 * @property Db             $db
 * @property Elements       $element
 */
class Application extends ServiceLocator{


    public $charset = 'UTF-8';

    private $_services = [];

    /**
     * @param string $name
     * @return mixed
     * @throws \anu\base\InvalidConfigException
     */
    public function __get($name){
        if(!isset($this->_services[$name])){
            //$camelCase  = \anu\helper\LetterCase::camel($name);
            $className = ucfirst($name);
            $withNameSpace = 'anu\service\\' . $className;
            $this->_services[$name] = \Anu::createObject($withNameSpace);;
        }

        return $this->_services[$name];
    }

    public function __set($name, $value){
        // TODO: Implement __set() method.
    }

    /**
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    public function handleRequest(){
        $this->request->handleRequest();
    }

    /**
     * Returns a [[Connection]] with the default configuration
     * this is actually a shortcut for $this->get('db') and boosts the performance since you can access it directly
     *
     * @return Connection|object
     * @throws \anu\base\InvalidConfigException
     */
    public function getDb()
    {
        return $this->get('db');
    }

    /**
     * @return User
     * @throws \anu\base\InvalidConfigException
     */
    public function getUser(){
        return $this->__get('user');
    }

    /**
     * @return Elements
     * @throws \anu\base\InvalidConfigException
     */
    public function getElements(){
        return $this->__get('elements');
    }

    /**
     * @return Config
     * @throws \anu\base\InvalidConfigException
     */
    public function getConfig(){
        return $this->__get('config');
    }

    /**
     * @return Request
     * @throws \anu\base\InvalidConfigException
     */
    public function getRequest(){
        return $this->__get('request');
    }

    /**
     * @return Session
     * @throws \anu\base\InvalidConfigException
     */
    public function getSession(){
        return $this->__get('session');
    }


    /**
     * @return Fields
     * @throws \anu\base\InvalidConfigException
     */
    public function getFields(){
        return $this->__get('fields');
    }

    /**
     * @return Template
     * @throws \anu\base\InvalidConfigException
     */
    public function getTemplate(){
        return $this->__get('template');
    }

    /**
     * @return Content
     * @throws \anu\base\InvalidConfigException
     */
    public function getContent(){
        return $this->__get('content');
    }

    /**
     * @return Sections
     * @throws \anu\base\InvalidConfigException
     */
    public function getSections(){
        return $this->__get('sections');
    }

    /**
     * @param string $id
     * @param bool $throwException
     * @return null|object
     * @throws \anu\base\InvalidConfigException
     */
    public function get($id, $throwException = true)
    {
        if (!isset($this->module)) {
            return parent::get($id, $throwException);
        }

        $component = parent::get($id, false);
        if ($component === null) {
            $component = $this->module->get($id, $throwException);
        }
        return $component;
    }

}