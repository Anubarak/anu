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
use anu\base\ElementNotFoundException;
use anu\base\EntryTypeNotFoundException;
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
    public function actionRenderList()
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


    // private functions
    //==========================================================
    /**
     * @return \anu\elements\Entry
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\ElementNotFoundException
     */
    public function _getEntry(): \anu\elements\Entry
    {
        $request = Anu::$app->getRequest();
        $id = $request->getParam('id');
        if (is_numeric($id)) {
            if (!$entry = \anu\elements\Entry::find()->id(1)->enabled(0)->one()) {
                throw new ElementNotFoundException('could not find Element with id ' . $id);
            }
        } else {
            $entry = new \anu\elements\Entry();
            /** @var \anu\models\Section $section */
            $section = $this->getSection($request->getParam('section'));
            $entry->sectionId = $section->id;
        }

        return $entry;
    }

    /**
     * @param $handle
     *
     * @return \anu\models\Section|null
     * @throws \anu\base\InvalidConfigException
     */
    private function getSection($handle)
    {
        if ($this->_section !== null) {
            return $this->_section;
        }

        return $this->_section = Anu::$app->getSections()->getSectionByHandle($handle);
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
        $fieldsLocation = $request->getParam('fieldsLocation', 'fields');
        $entry->setFieldValuesFromRequest($fieldsLocation);

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