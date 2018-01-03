<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 23.12.2017
 * Time: 19:22
 */

namespace anu\service;
use Anu;
use anu\base\Component;
use anu\base\InvalidRouteException;

class Config extends Component{

    /**
     * @var array;
     */
    private $_dbConfig;

    /**
     * @var array
     */
    private $_general;

    /**
     * Get the paths of the templates for the specified mode
     * Template::TEMPLATE_MODE_CP = 'cp'
     * Template::TEMPLATE_MODE_SITE = 'site'
     *
     * @see Template
     * @param $templateMode
     * @return mixed            path of template
     * @throws InvalidRouteException
     */
    public function getTemplatePath($templateMode){
        $general = $this->getGeneral();
        return $general['templatePaths'][$templateMode];
    }

    /**
     * Get general configuration
     *
     * @return mixed
     * @throws InvalidRouteException
     */
    public function getGeneral(){
        if(!$this->_general){
            $path = BASE_PATH . '/config/general.php';
            if(!file_exists($path)){
                throw new InvalidRouteException('File ' . BASE_PATH . '/config/general.php does not exist');
            }
            $this->_general = include BASE_PATH . '/config/general.php';
        }
        return $this->_general;
    }

    /**
     * Get db configuration
     *
     *      'database_type' => 'mysql',
     *      'driver'    => 'mysql',
     *      'database' => 'anu',
     *      'server' => 'localhost',
     *      'username' => 'root',
     *      'password' => ''
     *
     * @return array
     * @throws InvalidRouteException
     */
    public function getDb(){
        if(!$this->_dbConfig){
            $path = BASE_PATH . '/config/db.php';
            if(!file_exists($path)){
                throw new InvalidRouteException('File ' . BASE_PATH . '/config/db.php does not exist');
            }
            $this->_dbConfig = include BASE_PATH . '/config/db.php';
        }
        return $this->_dbConfig;
    }
}