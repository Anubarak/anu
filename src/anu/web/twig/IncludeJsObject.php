<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 31.12.2017
 * Time: 14:02
 */

namespace anu\web\twig;

class IncludeJsObject extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'includeJsObject' => new \Twig_SimpleFunction('includeJsObject', array($this, 'includeJsObject'))
        );
    }

    public function getName()
    {
        return 'js_object_twig_extension';
    }

    public function includeJsObject($object, $key)
    {
        \Anu::$app->getTemplate()->addAnuJsObject($object, $key);
        return true;
    }
}