<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 25.12.2017
 * Time: 12:16
 */

namespace anu\service;

use anu\base\Component;
use anu\base\Element;
use anu\base\ElementInterface;
use anu\db\Exception;
use anu\events\ElementEvent;
use anu\records\ElementRecord;

/**
 * Element service - handles all element action [[save]] [[delete]] and triggers events in their corresponding
 * elements
 *
 * @author Robin Schamabach
 */
class Elements extends Component{
    /**
     * @event ElementEvent The event that is triggered before an element is deleted.
     */
    const EVENT_BEFORE_DELETE_ELEMENT = 'beforeDeleteElement';

    /**
     * @event ElementEvent The event that is triggered after an element is deleted.
     */
    const EVENT_AFTER_DELETE_ELEMENT = 'afterDeleteElement';

    /**
     * @event ElementEvent The event that is triggered before an element is saved.
     */
    const EVENT_BEFORE_SAVE_ELEMENT = 'beforeSaveElement';

    /**
     * @event ElementEvent The event that is triggered after an element is saved.
     */
    const EVENT_AFTER_SAVE_ELEMENT = 'afterSaveElement';

    /**
     * @param ElementInterface $element
     * @param bool $runValidation
     * @return bool
     * @throws \anu\base\InvalidConfigException
     * @throws \Throwable
     * @throws Exception
     */
    public function saveElement(ElementInterface $element, $runValidation = true):bool
    {
        /** @var Element $element */
        $isNewElement = !$element->id;
        // Fire a 'beforeSaveElement' event
        if ($this->hasEventHandlers(self::EVENT_BEFORE_SAVE_ELEMENT)) {
            $this->trigger(self::EVENT_BEFORE_SAVE_ELEMENT, new ElementEvent([
                'element' => $element,
                'isNew' => $isNewElement
            ]));
        }

        if (!$element->beforeSave($isNewElement)) {
            return false;
        }

        // Validate
        if ($runValidation && !$element->validate()) {
            return false;
        }

        $transaction = \Anu::$app->getDb()->beginTransaction();
        try {
            // Get the element record
            if (!$isNewElement) {
                $elementRecord = ElementRecord::findOne($element->id);

                if (!$elementRecord) {
                    throw new \anu\base\Exception("No element exists with the ID '{$element->id}'");
                }
            } else {
                $elementRecord = new ElementRecord();
                $elementRecord->type = get_class($element);
            }
            // set attributes
            $elementRecord->fieldLayoutId = $element->fieldLayoutId;
            $elementRecord->enabled = (bool)$element->enabled;
            $elementRecord->archived = (bool)$element->archived;

            // Save the element record
            $elementRecord->save(false);

            $dateCreated = $elementRecord->dateCreated;

            if ($dateCreated === false) {
                throw new Exception('There was a problem calculating dateCreated.');
            }

            $dateUpdated = $elementRecord->dateUpdated;

            if ($dateUpdated === false) {
                throw new Exception('There was a problem calculating dateUpdated.');
            }

            // Save the new dateCreated and dateUpdated dates on the model
            $element->dateCreated = $dateCreated;
            $element->dateUpdated = $dateUpdated;

            if ($isNewElement) {
                // Save the element ID on the element model
                $element->id = $elementRecord->id;
                $element->uid = $elementRecord->uid;
            }

            // Save the content
            if (false && $element::hasContent()) {
                \Anu::$app->getContent()->saveContent($element);
            }

            // It is now officially saved
            $element->afterSave($isNewElement);
            $transaction->commit();
        } catch (\Throwable $e) {
            $transaction->rollBack();
            throw $e;
        }

        // Fire an 'afterSaveElement' event
        if ($this->hasEventHandlers(self::EVENT_AFTER_SAVE_ELEMENT)) {
            $this->trigger(self::EVENT_AFTER_SAVE_ELEMENT, new ElementEvent([
                'element' => $element,
                'isNew' => $isNewElement,
            ]));
        }
        return true;
    }
}