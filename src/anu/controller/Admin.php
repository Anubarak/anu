<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 25.12.2017
 * Time: 12:20
 */

namespace anu\controller;
use Anu;
use anu\base\Controller;
use anu\elements\User;
use anu\helper\Url;
use anu\records\UserRecord;

/**
 * Admin Controller, this class handles the first requests and redirects for the cp panel
 *
 * @author Robin Schambach
 */
class Admin extends Controller{

    /**
     * @return string
     * @throws \Throwable
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\db\Exception
     */
    public function actionIndex(){
        if(Anu::$app->getUser()->currentUser()){
            return $this->_renderDashBoard();
        }

        return $this->_renderLogin();
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    public function actionDashboard(){
        $this->redirectToLogin();
        return $this->_renderDashBoard();
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    public function actionSections(){
        $this->redirectToLogin();
        return Anu::$app->getTemplate()->render('pages/sections.twig', [
            'sections'  => Anu::$app->getSections()->getAllSections()
        ]);
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    public function actionFields(){
        $this->redirectToLogin();
        return \Anu::$app->template->render('pages/fields.twig', [

        ]);
    }

    // private functions
    //========================================================
    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    private function _renderDashBoard(){
        return \Anu::$app->template->render('pages/index.twig', [
        ]);
    }

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    private function _renderLogin(){
        return \Anu::$app->template->render('pages/login.twig', [
            'redirect'  => Anu::$app->getSession()->get('redirectUrl', 'admin/dashboard')
        ]);
    }
}