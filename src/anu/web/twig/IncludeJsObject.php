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
    public function getFunctions(): array
    {
        return array(
            'includeJsObject' => new \Twig_SimpleFunction('includeJsObject', array($this, 'includeJsObject'))
        );
    }

    public function getName(): string
    {
        return 'js_object_twig_extension';
    }

    /**
     * @param $object
     * @param $key
     *
     * @return bool
     * @throws \anu\base\InvalidConfigException
     */
    public function includeJsObject($object, $key)
    {
        \Anu::$app->getTemplate()->addAnuJsObject($object, $key);

        return '';
    }
}