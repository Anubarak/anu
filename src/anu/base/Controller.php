<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 23.12.2017
 * Time: 17:30
 */

namespace anu\base;

use anu\helper\Url;

/**
 * Controller are invoked by the router, by default all requests with action/<controllerName>/action<MethodName>
 * can access those function. www.example.com/action/user/save-user would access the
 * anu\controller\user->actionSaveUser function
 *
 * All requests with an action parameter will access those functions as well
 * <form>
 *      <input type="hidden" value="user/save-user" name="action">
 * </form>
 *
 * @author Robin Schambach
 */
class Controller extends Component{

    /**
     * @param array $var
     * @return string
     */
    public function asJson($var = array())
    {
        return json_encode($var);
    }

    /**
     * @param $to
     * @throws InvalidRouteException
     * @throws InvalidConfigException
     */
    public function redirect($to){
        return \Anu::$app->getRequest()->redirect($to);
    }

    /**
     * Checks whether the request is an ajax request or not
     *
     * @return bool
     * @throws InvalidConfigException
     */
    public function isAjaxRequest(){
        return \Anu::$app->getRequest()->isAjaxRequest();
    }

    /**
     * Use this function if the request requires an ajax request
     *
     * @throws InvalidRouteException
     * @throws InvalidConfigException
     */
    public function requireAjaxRequest(){
        if(!\Anu::$app->getRequest()->isAjaxRequest()){
            throw new InvalidRouteException();
        }
    }

    /**
     * @throws InvalidRouteException
     * @throws InvalidConfigException
     */
    public function requireLogin(){
        if(!\Anu::$app->getUser()->currentUser()){
            throw new InvalidRouteException();
        }
    }

    /**
     * @throws InvalidConfigException
     * @throws InvalidRouteException
     */
    public function redirectToLogin(){
        if(!\Anu::$app->getUser()->currentUser()){
            \Anu::$app->getSession()->set('redirectUrl', Url::getCurrentUri());
            \Anu::$app->getRequest()->redirect(Url::to('admin'));
        }
    }
}