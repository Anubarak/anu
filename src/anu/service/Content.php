<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 30.12.2017
 * Time: 21:55
 */

namespace anu\service;

use anu\base\Component;
use anu\base\ElementInterface;
use anu\db\Exception;
use anu\events\ElementContentEvent;

class Content extends Component{
    // Constants
    // =========================================================================

    /**
     * @event ElementContentEvent The event that is triggered before an element's content is saved.
     */
    public const EVENT_BEFORE_SAVE_CONTENT = 'beforeSaveContent';
    /**
     * @event ElementContentEvent The event that is triggered after an element's content is saved.
     */
    public const EVENT_AFTER_SAVE_CONTENT = 'afterSaveContent';
    // Properties
    // =========================================================================

    /**
     * @var string
     */
    public $fieldContext = 'global';

    /**
     * @var string Table for all contentrecords
     */
    public $contentTable = '{{%content}}';

    /**
     * @var string Field Column prefix
     */
    public $fieldColumnPrefix = 'field_';

    /**
     * Saves an element's content.
     *
     * @param ElementInterface $element The element whose content we're saving.
     *
     * @return bool Whether the content was saved successfully. If it wasn't, any validation errors will be saved on the
     *                 element and its content model.
     * @throws Exception if $element has not been saved yet
     * @throws \anu\base\InvalidConfigException
     * @throws \anu\base\Exception
     */
    public function saveContent(ElementInterface $element): bool
    {
        /** @var \anu\base\Element $element */
        if (!$element->id) {
            throw new Exception('Cannot save the content of an unsaved element.');
        }

        $originalContentTable = $this->contentTable;
        $originalFieldColumnPrefix = $this->fieldColumnPrefix;
        $originalFieldContext = $this->fieldContext;

        $this->contentTable = $element->getContentTable();
        $this->fieldColumnPrefix = $element->getFieldColumnPrefix();
        $this->fieldContext = $element->getFieldContext();

        // Fire a 'beforeSaveContent' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_CONTENT)) {
            $this->trigger(
                self::EVENT_BEFORE_SAVE_CONTENT,
                new ElementContentEvent(
                    [
                        'element' => $element
                    ]
                )
            );
        }
        // Prepare the data to be saved
        $values = [
            'elementId' => $element->id,
        ];

        if ($element->hasTitles() && ($title = (string) $element->title) !== '') {
            $values['title'] = $title;
        }

        $fieldLayout = $element->getFieldLayout();
        if ($fieldLayout) {
            foreach ($fieldLayout->getFields() as $field) {
                /** @var \anu\models\Field $field */
                if ($field::hasContentColumn()) {
                    $column = $this->fieldColumnPrefix . $field->handle;
                    $values[$column] = \anu\helper\Db::prepareValueForDb($field->serializeValue($element->getFieldValue($field->handle), $element));
                }
            }
        }

        // Insert/update the DB row
        if ($element->contentId) {
            // Update the existing row
            \Anu::$app->getDb()->createCommand()->update($this->contentTable, $values, ['id' => $element->contentId])->execute();
        } else {
            // Insert a new row and store its ID on the element
            \Anu::$app->getDb()->createCommand()->insert($this->contentTable, $values)->execute();
            $element->contentId = \Anu::$app->getDb()->getLastInsertID($this->contentTable);
        }

        // Fire an 'afterSaveContent' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_CONTENT)) {
            $this->trigger(
                self::EVENT_AFTER_SAVE_CONTENT,
                new ElementContentEvent(
                    [
                        'element' => $element
                    ]
                )
            );
        }

        $this->contentTable = $originalContentTable;
        $this->fieldColumnPrefix = $originalFieldColumnPrefix;
        $this->fieldContext = $originalFieldContext;

        return true;
    }
}