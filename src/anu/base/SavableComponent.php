<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 31.12.2017
 * Time: 13:09
 */

namespace anu\base;

/**
 * Class SavableComponent
 *
 * @property $id integer
 * @package anu\base
 */
abstract class SavableComponent extends Model{
    use SavableComponentTrait;
    /**
     * @event ModelEvent The event that is triggered before the component is saved
     *
     * set [[ModelEvent::isValid]] to `false` to prevent the component from getting saved.
     */
    public const EVENT_BEFORE_SAVE = 'beforeSave';
    /**
     * @event ModelEvent The event that is triggered after the component is saved
     */
    public const EVENT_AFTER_SAVE = 'afterSave';
    /**
     * @event ModelEvent The event that is triggered before the component is deleted
     *
     * set [[ModelEvent::isValid]] to `false` to prevent the component from getting deleted.
     */
    public const EVENT_BEFORE_DELETE = 'beforeDelete';
    /**
     * @event \anu\base\Event The event that is triggered after the component is deleted
     */
    public const EVENT_AFTER_DELETE = 'afterDelete';

    /**
     * @inheritdoc
     */
    public function getIsNew(): bool
    {
        return (!$this->id || strpos($this->id, 'new') === 0);
    }

    public function primaryKey(){
        return [
            'id'    => $this->id
        ];
    }

    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public function settingsAttributes(): array
    {
        // By default, include all public, non-static properties that were not defined in an abstract class
        $class = new \ReflectionClass($this);
        $names = [];

        foreach ($class->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic() && !$property->getDeclaringClass()->isAbstract()) {
                $names[] = $property->getName();
            }
        }

        return $names;
    }
}