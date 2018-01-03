<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 28.12.2017
 * Time: 17:59
 */

namespace anu\controller;
use Anu;
use anu\base\Controller;
use anu\db\Query;
use anu\models\Field;
use anu\records\FieldGroupRecord;
use anu\records\FieldRecord;

class Fields extends Controller{


    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    public function actionRender(){
        $this->redirectToLogin();

        $field = $this->_getField();

        $this->_populateField($field);

        return Anu::$app->getTemplate()->render('fields/index.twig', [
            'field'     => $field
        ]);
    }

    /**
     * Save a group
     *
     * @return string
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    public function actionSaveGroup(){
        $this->requireLogin();

        $group = $this->_getGroup();
        $this->_populateGroup($group);

        if(Anu::$app->getFields()->saveGroup($group)){
            $response = [
                'success'   =>  true,
                'groupId'   => $group->id
            ];
        }else{
            $response = [
                'success'   => false,
                'errors'    => $group->getErrors()
            ];
        }

        return $this->asJson($response);
    }

    /**
     * @return string
     * @throws \Throwable
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\db\Exception
     */
    public function actionSaveField(){
        //$this->redirectToLogin();
        $success = false;
        $field = $this->_getField();
        $this->_populateField($field);

        if(Anu::$app->getFields()->saveField($field)){
            if($this->isAjaxRequest()){
                $redirect = Anu::$app->request->getParam('redirect');
                $response['success'] = true;
                $response['redirect'] = $redirect? \anu\helper\Url::to($redirect) : \anu\helper\Url::to('admin/fields');
                return $this->asJson($response);
            }

            return $this->redirect(\anu\helper\Url::to('admin/fields'));
        }

        return $this->asJson([
            'success'   => $success,
            'errors'    => $field->getErrors()

        ]);
    }

    /**
     * @return Field
     * @throws \anu\base\InvalidConfigException
     */
    public function _getField(){
        $request = Anu::$app->getRequest();
        $fieldId = $request->getParam('fieldId');

        if($fieldId !== null && $fieldId !== 'new'){
            $result = (new Query())
                ->select([
                    'fields.id',
                    'fields.dateCreated',
                    'fields.dateUpdated',
                    'fields.groupId',
                    'fields.name',
                    'fields.handle',
                    'fields.instructions',
                    'fields.type',
                    'fields.settings'
                ])
                ->from(['{{%fields}} fields'])
                ->orderBy(['fields.name' => SORT_ASC])
                ->where(['fields.id' => $fieldId])
                ->one();
            $class = Anu::$app->getRequest()->getParam('type', $result['type']);

            /** @var Field $field */
            $field = Anu::$container->get($class, [], $result);
        }else{
            $field = new Field();
        }

        return $field;
    }

    /**
     * @param Field $field
     * @throws \anu\base\InvalidConfigException
     */
    public function _populateField(Field $field){
        $request = Anu::$app->getRequest();
        $field->groupId = $request->getParam('groupId', $field->groupId);
        $field->handle = $request->getParam('handle', $field->handle);
        $field->id = $request->getParam('fieldId', $field->id);
        $field->type = $request->getParam('type', $field->type);
        $field->name = $request->getParam('name', $field->name);
        $field->instructions = $request->getParam('instructions', $field->instructions);
    }

    /**
     * @return FieldGroupRecord
     * @throws \anu\base\InvalidConfigException
     */
    public function _getGroup(){
        $request = Anu::$app->getRequest();
        $groupId = $request->getParam('groupId');
        if(is_int($groupId) && $groupId !== null){
            $group = FieldGroupRecord::find()->where(['id' => $groupId])->one();
        }else{
            $group = new FieldGroupRecord();
        }

        return $group;
    }

    /**
     * @param FieldGroupRecord $group
     * @throws \anu\base\InvalidConfigException
     */
    public function _populateGroup(FieldGroupRecord $group){
        $request = Anu::$app->getRequest();
        $group->id = $request->getParam('groupId', $group->id);
        $group->name = $request->getParam('name', $group->name);
    }

}