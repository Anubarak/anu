<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 31.12.2017
 * Time: 18:52
 */

namespace anu\service;

use Anu;
use anu\base\Component;
use anu\base\EntryTypeNotFoundException;
use anu\base\SectionNotFoundException;
use anu\db\Query;
use anu\events\EntryTypeEvent;
use anu\events\SectionEvent;
use anu\models\EntryType;
use anu\models\Section;
use anu\records\EntryTypeRecord;
use anu\records\SectionRecord;

class Sections extends Component{




    /**
     * @event ElementEvent The event that is triggered before an element is deleted.
     */
    const EVENT_BEFORE_DELETE_SECTION = 'beforeDeleteSection';

    /**
     * @event ElementEvent The event that is triggered after an element is deleted.
     */
    const EVENT_AFTER_DELETE_SECTION = 'afterDeleteSection';

    /**
     * @event ElementEvent The event that is triggered before an element is saved.
     */
    const EVENT_BEFORE_SAVE_SECTION = 'beforeSaveSection';

    /**
     * @event ElementEvent The event that is triggered after an element is saved.
     */
    const EVENT_AFTER_SAVE_SECTION = 'afterSaveSection';

    /**
     * @event EntryTypeEvent The event that is triggered before an entry type is saved.
     */
    const EVENT_BEFORE_SAVE_ENTRY_TYPE = 'beforeSaveEntryType';

    /**
     * @event EntryTypeEvent The event that is triggered after an entry type is saved.
     */
    const EVENT_AFTER_SAVE_ENTRY_TYPE = 'afterSaveEntryType';

    /**
     * @event EntryTypeEvent The event that is triggered before an entry type is deleted.
     */
    const EVENT_BEFORE_DELETE_ENTRY_TYPE = 'beforeDeleteEntryType';

    /**
     * @event EntryTypeEvent The event that is triggered after an entry type is deleted.
     */
    const EVENT_AFTER_DELETE_ENTRY_TYPE = 'afterDeleteEntryType';

    // private properties
    //===============================================================

    private $_sectionsById;

    /**
     * @return Section[]
     * @throws \anu\base\InvalidConfigException
     */
    public function getAllSections(){
        if($this->_sectionsById !== null){
            return $this->_sectionsById;
        }

        $sectionRecords = SectionRecord::find()->all();
        $sections = [];
        foreach($sectionRecords as $record){
            $section = new Section($record->getAttributes());
            $sections[] = $section;
            $this->_sectionsById[$section->id] = $section;
        }

        Anu::$app->getTemplate()->addAnuJsObject($sections, 'sections');

        return $this->_sectionsById;
    }


    /**
     * Saves a section.
     *
     * @param Section $section       The section to be saved
     * @param bool    $runValidation Whether the section should be validated
     *
     * @return bool
     * @throws SectionNotFoundException if $section->id is invalid
     * @throws \Throwable if reasons
     */
    public function saveSection(Section $section, bool $runValidation = true): bool
    {
        $isNewSection = !$section->id;
        // Fire a 'beforeSaveSection' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_SECTION)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_SECTION, new SectionEvent([
                'section' => $section,
                'isNew' => $isNewSection
            ]));
        }

        if ($runValidation && !$section->validate()) {
            return false;
        }

        if (!$isNewSection) {
            $sectionRecord = SectionRecord::find()
                ->where(['id' => $section->id])
                ->one();

            if (!$sectionRecord) {
                throw new SectionNotFoundException("No section exists with the ID '{$section->id}'");
            }

            $oldSection = new Section($sectionRecord->getAttributes());
        } else {
            $sectionRecord = new SectionRecord();
        }

        // Main section settings
        /** @var SectionRecord $sectionRecord */
        $sectionRecord->name = $section->name;
        $sectionRecord->handle = $section->handle;
        $sectionRecord->type = $section->type;

        $db = Anu::$app->getDb();
        $transaction = $db->beginTransaction();
        try{
            // Do we need to create a structure?
            $sectionRecord->save(false);
            // Now that we have a section ID, save it on the model
            if($isNewSection){
                $section->id = $sectionRecord->id;
            }
            // Make sure there's at least one entry type for this section
            // -----------------------------------------------------------------
            if(!$isNewSection){
                $entryTypeExists = (new Query())->select(['id'])->from(['{{%entrytypes}}'])->where(['sectionId' => $section->id])->exists();
            }else{
                $entryTypeExists = false;
            }
            if(!$entryTypeExists){
                $entryType = new EntryType();
                $entryType->sectionId = $section->id;
                $entryType->name = $section->name;
                $entryType->handle = $section->handle;
                if($section->type === Section::TYPE_SINGLE){
                    $entryType->hasTitleField = false;
                    $entryType->titleLabel = null;
                    $entryType->titleFormat = '{section.name|raw}';
                }else{
                    $entryType->hasTitleField = true;
                    $entryType->titleLabel = Anu::t('anu', 'Title');
                    $entryType->titleFormat = null;
                }
                $this->saveEntryType($entryType);
            }
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        // Fire an 'afterSaveSection' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_SECTION)) {
            $this->trigger(self::EVENT_AFTER_SAVE_SECTION, new SectionEvent([
                'section' => $section,
                'isNew' => $isNewSection
            ]));
        }

        return true;
    }


    /**
     * Saves an entry type.
     *
     * @param EntryType $entryType     The entry type to be saved
     * @param bool      $runValidation Whether the entry type should be validated
     *
     * @return bool Whether the entry type was saved successfully
     * @throws EntryTypeNotFoundException if $entryType->id is invalid
     * @throws \Throwable if reasons
     */
    public function saveEntryType(EntryType $entryType, bool $runValidation = true): bool
    {
        $isNewEntryType = !$entryType->id;

        // Fire a 'beforeSaveEntryType' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_ENTRY_TYPE)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_ENTRY_TYPE, new EntryTypeEvent([
                'entryType' => $entryType,
                'isNew' => $isNewEntryType,
            ]));
        }

        if ($runValidation && !$entryType->validate()) {
            return false;
        }

        if ($entryType->id) {
            $entryTypeRecord = EntryTypeRecord::findOne($entryType->id);

            if (!$entryTypeRecord) {
                throw new EntryTypeNotFoundException("No entry type exists with the ID '{$entryType->id}'");
            }
        } else {
            $entryTypeRecord = new EntryTypeRecord();

            // Get the next biggest sort order
            $maxSortOrder = (new Query())
                ->from(['{{%entrytypes}}'])
                ->where(['sectionId' => $entryType->sectionId])
                ->max('[[sortOrder]]');

            $entryTypeRecord->sortOrder = $maxSortOrder ? $maxSortOrder + 1 : 1;
        }

        $entryTypeRecord->sectionId = $entryType->sectionId;
        $entryTypeRecord->name = $entryType->name;
        $entryTypeRecord->handle = $entryType->handle;
        $entryTypeRecord->hasTitleField = $entryType->hasTitleField;
        $entryTypeRecord->titleLabel = ($entryType->hasTitleField ? $entryType->titleLabel : null);
        $entryTypeRecord->titleFormat = (!$entryType->hasTitleField ? $entryType->titleFormat : null);

        $transaction = Anu::$app->getDb()->beginTransaction();
        try {
            // Save the field layout
            //$fieldLayout = $entryType->getFieldLayout();
            //Craft::$app->getFields()->saveLayout($fieldLayout);
            $entryType->fieldLayoutId = 0;//$fieldLayout->id;
            $entryTypeRecord->fieldLayoutId = 0;//$fieldLayout->id;

            // Save the entry type
            $entryTypeRecord->save(false);

            // Now that we have an entry type ID, save it on the model
            if (!$entryType->id) {
                $entryType->id = $entryTypeRecord->id;
            }

            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();

            throw $e;
        }

        // Fire an 'afterSaveEntryType' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_ENTRY_TYPE)) {
            $this->trigger(self::EVENT_AFTER_SAVE_ENTRY_TYPE, new EntryTypeEvent([
                'entryType' => $entryType,
                'isNew' => $isNewEntryType,
            ]));
        }

        return true;
    }
}