<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 23.12.2017
 * Time: 16:33
 */

namespace anu;

use anu\base\Container;
use anu\base\InvalidConfigException;
use anu\db\Exception;
use anu\web\Application;

DEFINE('ANU_PATH', __DIR__);

class BaseAnu{

    /** @var Application */
    public static $app;

    public static $classMap = [];

    /** @var \anu\di\Container */
    public static $container;

    public static function getVersion()
    {
        return '0.01';
    }

    public static $aliases = ['@anu' => __DIR__];

    public static function getAlias($alias, $throwException = true)
    {
        if (strncmp($alias, '@', 1)) {
            // not an alias
            return $alias;
        }

        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                return $pos === false ? static::$aliases[$root] : static::$aliases[$root] . substr($alias, $pos);
            }

            foreach (static::$aliases[$root] as $name => $path) {
                if (strpos($alias . '/', $name . '/') === 0) {
                    return $path . substr($alias, strlen($name));
                }
            }
        }

        if ($throwException) {
            throw new InvalidParamException("Invalid path alias: $alias");
        }

        return false;
    }

    public static function getRootAlias($alias)
    {
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);

        if (isset(static::$aliases[$root])) {
            if (is_string(static::$aliases[$root])) {
                return $root;
            }

            foreach (static::$aliases[$root] as $name => $path) {
                if (strpos($alias . '/', $name . '/') === 0) {
                    return $name;
                }
            }
        }

        return false;
    }

    public static function setAlias($alias, $path)
    {
        if (strncmp($alias, '@', 1)) {
            $alias = '@' . $alias;
        }
        $pos = strpos($alias, '/');
        $root = $pos === false ? $alias : substr($alias, 0, $pos);
        if ($path !== null) {
            $path = strncmp($path, '@', 1) ? rtrim($path, '\\/') : static::getAlias($path);
            if (!isset(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [$alias => $path];
                }
            } elseif (is_string(static::$aliases[$root])) {
                if ($pos === false) {
                    static::$aliases[$root] = $path;
                } else {
                    static::$aliases[$root] = [
                        $alias => $path,
                        $root => static::$aliases[$root],
                    ];
                }
            } else {
                static::$aliases[$root][$alias] = $path;
                krsort(static::$aliases[$root]);
            }
        } elseif (isset(static::$aliases[$root])) {
            if (is_array(static::$aliases[$root])) {
                unset(static::$aliases[$root][$alias]);
            } elseif ($pos === false) {
                unset(static::$aliases[$root]);
            }
        }
    }


    public static function autoload($className)
    {
        if (isset(static::$classMap[$className])) {
            $classFile = static::$classMap[$className];
            if ($classFile[0] === '@') {
                $classFile = static::getAlias($classFile);
            }
        } elseif (strpos($className, '\\') !== false) {
            $classFile = static::getAlias('@' . str_replace('\\', '/', $className) . '.php', false);
            if ($classFile === false || !is_file($classFile)) {
                return;
            }
        } else {
            return;
        }

        include $classFile;

        if (!class_exists($className, false) && !interface_exists($className, false) && !trait_exists($className, false)) {
            throw new \Exception("Unable to find '$className' in file: $classFile. Namespace missing?");
        }
    }

    public static function getObjectVars($object)
    {
        return get_object_vars($object);
    }

    /**
     * @throws InvalidConfigException
     */
    public static function init(){
        self::$app = new Application();
        self::$container = new \anu\di\Container();
        $whoops = new \Whoops\Run;
        $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
        $whoops->register();

        // register db
        self::$app->set('db', [
              'class' => 'anu\db\Connection',
              'dsn' => 'mysql:host=127.0.0.1;dbname=anu',
              'username' => 'root',
              'password' => '',
              'charset' => 'utf8',
        ]);

    }


    /**
     * Configures an object with the initial property values.
     * @param object $object the object to be configured
     * @param array $properties the property initial values given in terms of name-value pairs.
     * @return object the object itself
     */
    public static function configure($object, $properties)
    {
            foreach ($properties as $name => $value) {
                $object->$name = $value;
            }


        return $object;
    }


    public static function createObject($type, array $params = [], $debug = false)
    {
        if (is_string($type)) {
            return static::$container->get($type, $params);
        } elseif (is_array($type) && isset($type['class'])) {
            $class = $type['class'];
            unset($type['class']);
            return static::$container->get($class, $params, $type);
        } elseif (is_callable($type, true)) {
            return static::$container->invoke($type, $params);
        } elseif (is_array($type)) {
            throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
        }

        throw new InvalidConfigException('Unsupported configuration type: ' . gettype($type));
    }

    public static function t($category, $message, $params = [], $language = null)
    {
        return self::parse($message, $params);
    }


    public static function parse(
        /* string */ $subject,
                     array        $variables,
        /* string */ $escapeChar = '@',
        /* string */ $errPlaceholder = null
    ) {
        $esc = preg_quote($escapeChar);
        $expr = "/
        $esc$esc(?=$esc*+{)
      | $esc{
      | {(\w+)}
    /x";

        $callback = function($match) use($variables, $escapeChar, $errPlaceholder) {
            switch ($match[0]) {
                case $escapeChar . $escapeChar:
                    return $escapeChar;

                case $escapeChar . '{':
                    return '{';

                default:
                    if (isset($variables[$match[1]])) {
                        return $variables[$match[1]];
                    }

                    return isset($errPlaceholder) ? $errPlaceholder : $match[0];
            }
        };

        return preg_replace_callback($expr, $callback, $subject);
    }
}