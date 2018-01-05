<?php
/**
 * Created by PhpStorm.
 * User: scham
 * Date: 04.01.2018
 * Time: 14:41
 */

namespace anu\behaviors;

use Anu;
use anu\base\Behavior;
use anu\base\InvalidConfigException;
use anu\models\FieldLayout;

class FieldLayoutBehavior extends Behavior
{
    // Properties
    // =========================================================================

    /**
     * @var string|null The element type that the field layout will be associated with
     */
    public $elementType;
    /**
     * @var string The name of the attribute on the owner class that is used to store the field layout’s ID
     */
    public $idAttribute = 'fieldLayoutId';
    /**
     * @var \anu\models\FieldLayout|null The field layout associated with the owner
     */
    private $_fieldLayout;
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     *
     * @throws \anu\base\InvalidConfigException if the behavior was not configured properly
     */
    public function init()
    {
        parent::init();

        if ($this->elementType === null) {
            throw new InvalidConfigException('The element type has not been set.');
        }

        if ($this->idAttribute === null) {
            throw new InvalidConfigException('The ID attribute has not been set.');
        }
    }

    /**
     * Returns the owner's field layout.
     *
     * @return FieldLayout
     * @throws \anu\base\InvalidConfigException
     */
    public function getFieldLayout(): FieldLayout
    {
        if ($this->_fieldLayout === null) {
            if ($id = $this->owner->{$this->idAttribute}) {
                $this->_fieldLayout = Anu::$app->getFields()->getLayoutById($id);
            }

            /** @noinspection NotOptimalIfConditionsInspection */
            if ($this->_fieldLayout === null) {
                $this->_fieldLayout = new FieldLayout();
                $this->_fieldLayout->type = $this->elementType;
            }
        }

        return $this->_fieldLayout;
    }

    /**
     * Sets the owner's field layout.
     *
     * @param FieldLayout $fieldLayout
     *
     * @return void
     */
    public function setFieldLayout(FieldLayout $fieldLayout): void
    {
        $this->_fieldLayout = $fieldLayout;
    }
}