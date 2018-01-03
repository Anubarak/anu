<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 31.12.2017
 * Time: 17:39
 */

namespace anu\controller;
use Anu;
use anu\base\Controller;
use anu\models\EntryType;
use anu\models\Section;
use anu\records\EntryTypeRecord;
use anu\records\SectionRecord;

class Sections extends Controller{


    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    public function actionRender(){
        $section = $this->_getSection();
        $this->_populateSection($section);

        $this->redirectToLogin();
        return Anu::$app->getTemplate()->render('sections/index.twig', [
            'section'   => $section
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
    public function actionRenderEntryTypes(){
        $this->redirectToLogin();

        $section = $this->_getSection();
        $this->_populateSection($section);

        return Anu::$app->getTemplate()->render('sections/entryTypes.twig', [
            'section'       => $section,
            'entryTypes'    => $section->getEntryTypes()
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
    public function actionRenderEntryType(){
        $entryType = $this->_getEntryType();

        return Anu::$app->getTemplate()->render('sections/entryTypeForm.twig', [
            'entryType'       => $entryType,
        ]);
    }


    /**
     * @return string
     * @throws \Throwable
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     * @throws \anu\base\SectionNotFoundException
     */
    public function actionSaveSection(){
        $this->requireLogin();

        $response = [];

        $section = $this->_getSection();
        $this->_populateSection($section);

        if(Anu::$app->getSections()->saveSection($section)){
            if($this->isAjaxRequest()){
                $redirect = Anu::$app->request->getParam('redirect');
                $response['success'] = true;
                $response['redirect'] = $redirect? \anu\helper\Url::to($redirect) : \anu\helper\Url::to('admin/fields');

                return $this->asJson($response);
            }

            return $this->redirect(\anu\helper\Url::to('admin/fields'));
        }

        return $this->asJson([
            'success'   => false,
            'error'     => $section->getErrors()
        ]);
    }


    // private
    //===================================================================


    /**
     * @return Section
     * @throws \anu\base\InvalidConfigException
     */
    private function _getSection(){
        $request = Anu::$app->getRequest();
        $id = $request->getParam('id');

        if(is_numeric($id) && $id !== null){
            $sectionRecord = SectionRecord::find()->where(['id' => $id])->one();
            $section = new Section($sectionRecord->getAttributes());
        }else{
            $section = new Section();
        }

        return $section;
    }

    /**
     * @param $section
     * @throws \anu\base\InvalidConfigException
     */
    private function _populateSection($section){
        $request = Anu::$app->getRequest();
        $section->handle = $request->getParam('handle', $section->handle);
        $id = $request->getParam('id', $section->id);
        $section->id = is_numeric($id)? $id : null;
        $section->type = $request->getParam('type', $section->type);
        $section->name = $request->getParam('name', $section->name);
    }

    /**
     * @return $entryType EntryType
     * @throws \anu\base\InvalidConfigException
     */
    private function _getEntryType(){
        $request = Anu::$app->getRequest();
        $id = $request->getParam('entryTypeId');

        if(is_numeric($id) && $id !== null){
            $entryTypeRecord = EntryTypeRecord::find()->where(['id' => $id])->one();
            $entryType = new EntryType($entryTypeRecord->getAttributes());
        }else{
            $entryType = new EntryType();
        }

        return $entryType;
    }

    /**
     * @param $entryType EntryType
     * @throws \anu\base\InvalidConfigException
     */
    private function _populateEntryType(EntryType $entryType){
        $request = Anu::$app->getRequest();
        $entryType->handle = $request->getParam('handle', $entryType->handle);
        $id = $request->getParam('enrtyTypeId', $entryType->id);
        $entryType->id = is_numeric($id)? $id : null;
        $entryType->type = $request->getParam('type', $entryType->type);
        $entryType->name = $request->getParam('name', $entryType->name);
    }
}