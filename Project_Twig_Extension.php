<?php
/**
 * Created by PhpStorm.
 * User: scham
 * Date: 02.01.2018
 * Time: 11:46
 */

class Project_Twig_Extension extends Twig_Extension implements Twig_Extension_GlobalsInterface
{
    public function getGlobals()
    {
        return [
            'anu'   => new \anu\web\Application(),
            'entry' => new \anu\base\Model(),
        ];
    }
}