<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 23.12.2017
 * Time: 17:33
 */

namespace anu\controller;

use anu\base\Controller;
use anu\base\Model;
use anu\db\Query;
use anu\migrations\Install;
use anu\models\Field;

class Home extends Controller{

    public function actionIndex(){


        return \Anu::$app->template->render('home.twig', [
            'digit' => \Anu::$app->request->getParam('controller'),
            'word' => \Anu::$app->request->getParam('action')
        ]);
    }
}