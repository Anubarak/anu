<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 27.12.2017
 * Time: 17:42
 */

namespace anu\helper;

//TODO generate PHPDoc
use anu\base\Model;

class Url
{

    public static $serverBasePath;

    /**
     * Returns valid URL
     *
     * @param null $to
     * @return null|string
     */
    public static function to($to = null){
        $location = $to;
        if($location === null){
            $location = BASE_URL;
        }

        if($to instanceof Model){
            $location = $to->getUrl();
        }

        if(strpos($location, BASE_URL) === false){
            $location = BASE_URL  . $location;
        }

        return $location;
    }

    /**
     * Define the current relative URI.
     *
     * @return string
     */
    public static function getCurrentUri()
    {
        // Get the current Request URI and remove rewrite base path from it (= allows one to run the router in a sub folder)
        $uri = substr($_SERVER['REQUEST_URI'], strlen(self::getBasePath()));

        // Don't take query params into account on the URL
        if (strstr($uri, '?')) {
            $uri = substr($uri, 0, strpos($uri, '?'));
        }

        // Remove trailing slash + enforce a slash at the start
        return '/'.trim($uri, '/');
    }

    /**
     * Return server base Path, and define it if isn't defined.
     *
     * @return string
     */
    public static function getBasePath()
    {
        // Check if server base path is defined, if not define it.
        if (self::$serverBasePath=== null) {
            self::$serverBasePath = implode('/', array_slice(explode('/', $_SERVER['SCRIPT_NAME']), 0, -1)).'/';
        }

        return self::$serverBasePath;
    }

    public static function getFullPath(){
        $path = BASE_URL . self::getCurrentUri();
        return str_replace('//', '/', $path);
    }

}