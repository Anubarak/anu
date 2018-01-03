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
// TODO generate PHP doc
class Session extends Component{

    /**
     *
     */
    const ERROR_KEY = 'errors';

    /**
     *
     */
    const NOTICE_KEY = 'notices';

    /**
     * Returns the session variable value with the session variable name.
     * If the session variable does not exist, the `$defaultValue` will be returned.
     * @param string $key the session variable name
     * @param mixed $defaultValue the default value to be returned when the session variable does not exist.
     * @return mixed the session variable value, or $defaultValue if the session variable does not exist.
     */
    public function get($key, $defaultValue = null)
    {
        $this->open();
        return isset($_SESSION[$key]) ? $_SESSION[$key] : $defaultValue;
    }

    /**
     * Adds a session variable.
     * If the specified name already exists, the old value will be overwritten.
     * @param string $key session variable name
     * @param mixed $value session variable value
     */
    public function set($key, $value)
    {
        $this->open();
        $_SESSION[$key] = $value;
    }

    /**
     * Removes a session variable.
     * @param string $key the name of the session variable to be removed
     * @return mixed the removed value, null if no such session variable.
     */
    public function remove($key)
    {
        $this->open();
        if (isset($_SESSION[$key])) {
            $value = $_SESSION[$key];
            unset($_SESSION[$key]);

            return $value;
        }

        return null;
    }

    /**
     * Removes all session variables.
     */
    public function removeAll()
    {
        $this->open();
        foreach (array_keys($_SESSION) as $key) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * @return bool whether the session has started
     */
    public function getIsActive()
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }


    /**
     * @param mixed $key session variable name
     * @return bool whether there is the named session variable
     */
    public function has($key)
    {
        $this->open();
        return isset($_SESSION[$key]);
    }


    /**
     * @return bool
     */
    public function open(){
        if(!$this->getIsActive()){
            @session_start();
        }
        return true;
    }

    /**
     * @param $message
     */
    public function addError($message){
        $this->open();
        $_SESSION[self::ERROR_KEY][] = $message;
    }

    /**
     * @return array
     */
    public function getErrors(){
        $response = $this->has(self::ERROR_KEY)? $_SESSION[self::ERROR_KEY] : [];
        $this->remove(self::ERROR_KEY);
        return $response;
    }

    /**
     * @param $message
     */
    public function addNotice($message){
        $this->open();
        $_SESSION[self::NOTICE_KEY][] = $message;
    }

    /**
     * @return array
     */
    public function getNotices(){
        $response = $this->has(self::NOTICE_KEY)? $_SESSION[self::NOTICE_KEY] : [];
        $this->remove(self::NOTICE_KEY);
        return $response;
    }

    public function getAllFlashs(){
        return [
            'errors'    => $this->getErrors(),
            'notices'   => $this->getNotices()
        ];
    }

}