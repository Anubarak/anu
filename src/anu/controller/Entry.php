<?php
/**
 * Created by PhpStorm.
 * User: scham
 * Date: 05.01.2018
 * Time: 12:12
 */

namespace anu\controller;

use Anu;
use anu\base\Controller;
use anu\base\Element;
use anu\base\ElementNotFoundException;
use anu\base\EntryTypeNotFoundException;
use anu\helper\Url;
use anu\models\Section;
use anu\records\SectionRecord;

class Entry extends Controller
{
    /** @var \anu\models\Section $_section */
    private $_section;

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    public function actionRenderList(): string
    {
        $handle = Anu::$app->getRequest()->getParam('section');
        return Anu::$app->getTemplate()->render(
            'entries/index.twig',
            [
                'section' => SectionRecord::find()->where(['handle' => $handle])->one()
            ]
        );
    }

    /**
     * @throws \Twig_Error_Syntax
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Loader
     * @throws \anu\base\ElementNotFoundException
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\InvalidRouteException
     */
    public function actionRenderForm()
    {
        $this->redirectToLogin();

        $entry = $this->_getEntry();
        $this->_populateEntryModel($entry);

        $section = $this->getSection(Anu::$app->getRequest()->getParam('section'));

        return Anu::$app->getTemplate()->render(
            'entries/form.twig',
            [
                'section' => $section,
                'entry'   => $entry
            ]
        );
    }

    /**
     * @throws \anu\base\InvalidParamException
     * @throws \anu\base\ElementNotFoundException
     * @throws \anu\db\Exception
     * @throws \Throwable
     * @throws \anu\base\InvalidConfigException
     */
    public function actionSaveEntry()
    {
        $entry = $this->_getEntry();
        $this->_populateEntryModel($entry);
        $currentUser = Anu::$app->getUser()->currentUser();

        //TODO check for permission
        if ($entry->enabled) {
            $entry->setScenario(Element::SCENARIO_LIVE);
        }

        if (!Anu::$app->getElements()->saveElement($entry)) {
            if ($this->isAjaxRequest()) {
                return $this->asJson(
                    [
                        'errors'  => $entry->getErrors(),
                        'success' => false
                    ]
                );
            }
            Anu::$app->getSession()->addError(Anu::t('anu', 'Couldn’t save entry.'));


            // Send the entry back to the template
            Anu::$app->getSession()->setRouteParams(
                [
                    'entry' => $entry
                ]
            );

            return null;
        }

        if ($this->isAjaxRequest()) {
            $return = [];

            $return['success'] = true;
            $return['id'] = $entry->id;
            $return['title'] = $entry->title;

            if (Anu::$app->getRequest()->isCpRequest()) {
                $return['cpEditUrl'] = $entry->getCpEditUrl();
                $return['redirect'] = Url::to('admin/entries/' . $entry->getSection()->handle);
            }

            $return['authorUsername'] = $entry->getAuthor()->username;
            $return['dateCreated'] = $entry->dateCreated;
            $return['dateUpdated'] = $entry->dateUpdated;
            $return['postDate'] = ($entry->postDate ?: null);

            return $this->asJson($return);
        }
    }


    // private functions
    //==========================================================
    /**
     * @return \anu\elements\Entry
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\ElementNotFoundException
     */
    private function _getEntry(): \anu\elements\Entry
    {
        $request = Anu::$app->getRequest();
        $id = $request->getParam('id');
        if (is_numeric($id)) {
            if (!$entry = \anu\elements\Entry::find()->id($id)->enabled(0)->one()) {
                throw new ElementNotFoundException('could not find Element with id ' . $id);
            }
        } else {
            $entry = new \anu\elements\Entry();
            $sectionIdentifier = $request->getParam('section', (int) $request->getParam('sectionId'));
            /** @var \anu\models\Section $section */
            $section = $this->getSection($sectionIdentifier);
            $entry->sectionId = $section->id;
        }

        return $entry;
    }

    /**
     * @param $id
     *
     * @return \anu\models\Section|null
     * @throws \anu\base\InvalidConfigException
     */
    private function getSection($id): ?Section
    {
        if ($this->_section !== null) {
            return $this->_section;
        }
        if (is_numeric($id)) {
            return $this->_section = Anu::$app->getSections()->getSectionById($id);
        }

        return $this->_section = Anu::$app->getSections()->getSectionByHandle($id);
    }

    /**
     * @param \anu\elements\Entry $entry
     *
     * @throws \anu\base\InvalidConfigException
     */
    public function _populateEntryModel($entry)
    {
        $request = Anu::$app->getRequest();
        $entry->typeId = $request->getParam('typeId', $entry->typeId);
        $entry->slug = $request->getParam('slug', $entry->slug);
        if (($postDate = $request->getParam('postDate')) !== false) {
            $entry->postDate = \DateTime::createFromFormat('d-m-y H:i:s', $postDate) ?: null;
        }

        if (($expiryDate = $request->getParam('expiryDate')) !== false) {
            $entry->expiryDate = \DateTime::createFromFormat('d-m-y H:i:s', $expiryDate) ?: null;
        }

        $entry->enabled = (bool) $request->getParam('enabled', $entry->enabled);
        $entry->title = $request->getParam('title', $entry->title);

        if (!$entry->typeId) {
            // Default to the section's first entry type
            $entry->typeId = $entry->getSection()->getEntryTypes()[0]->id;
        }

        $entry->fieldLayoutId = $entry->getType()->fieldLayoutId;
        $entry->setFieldValuesFromRequest();

        // Author
        $currentUser = Anu::$app->getUser()->currentUser();
        $currentUserId = $currentUser ? $currentUser->id : 0;
        $authorId = $request->getParam('author', ($entry->authorId ?: $currentUserId));

        if (\is_array($authorId)) {
            $authorId = $authorId[0] ?? null;
        }

        $entry->authorId = $authorId;

        // Parent
        if (($parentId = $request->getParam('parentId')) !== null) {
            if (\is_array($parentId)) {
                $parentId = reset($parentId) ?: '';
            }

            $entry->newParentId = $parentId ?: '';
        }
    }
}