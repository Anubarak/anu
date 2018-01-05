<?php
/**
 * Created by PhpStorm.
 * User: scham
 * Date: 04.01.2018
 * Time: 11:55
 */

namespace anu\models;

use Anu;
use anu\base\Model;

class FieldLayout extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null ID
     */
    public $id;
    /**
     * @var string|null Type
     */
    public $type;
    /**
     * @var
     */
    private $_tabs;
    /**
     * @var
     */
    private $_fields;
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['id'], 'number', 'integerOnly' => true],
        ];
    }

    /**
     * Returns the layout’s fields’ IDs.
     *
     * @return array The layout’s fields’ IDs.
     * @throws \anu\base\InvalidConfigException
     */
    public function getFieldIds(): array
    {
        $ids = [];

        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            $ids[] = $field->id;
        }

        return $ids;
    }

    /**
     * Returns the layout’s fields.
     *
     * @return \anu\models\Field[] The layout’s fields.
     * @throws \anu\base\InvalidConfigException
     */
    public function getFields(): array
    {
        if ($this->_fields !== null) {
            return $this->_fields;
        }

        if (!$this->id) {
            return [];
        }

        return $this->_fields = Anu::$app->getFields()->getFieldsByLayoutId($this->id);
    }

    /**
     * Sets the layout']”s fields.
     *
     * @param \anu\models\Field[] $fields      An array of the layout’s fields, which can either be
     *                                         FieldLayoutFieldModel objects or arrays defining the tab’s
     *                                         attributes.
     *
     * @return void
     */
    public function setFields(array $fields): void
    {
        $this->_fields = $fields;
    }

    /**
     * Returns a field by its handle.
     *
     * @param string $handle The field handle.
     *
     * @return Field|null
     * @throws \anu\base\InvalidConfigException
     */
    public function getFieldByHandle(string $handle): ?Field
    {
        foreach ($this->getFields() as $field) {
            /** @var Field $field */
            if ($field->handle === $handle) {
                return $field;
            }
        }

        return null;
    }

    /**
     * Json serialize interface
     *
     * @return array
     * @throws \anu\base\InvalidConfigException
     */
    public function jsonSerialize(): array
    {
        return array_merge(
            $this->getAttributes(),
            [
                'tabs' => $this->getTabs()
            ]
        );
    }

    /**
     * Returns the layout’s tabs.
     *
     * @return FieldLayoutTab[] The layout’s tabs.
     * @throws \anu\base\InvalidConfigException
     */
    public function getTabs(): array
    {
        if ($this->_tabs !== null) {
            return $this->_tabs;
        }

        if (!$this->id) {
            return [];
        }

        return $this->_tabs = Anu::$app->getFields()->getLayoutTabsById($this->id);
    }

    /**
     * Sets the layout’s tabs.
     *
     * @param array|FieldLayoutTab[] $tabs An array of the layout’s tabs, which can either be FieldLayoutTab
     *                                     objects or arrays defining the tab’s attributes.
     *
     * @return void
     */
    public function setTabs($tabs): void
    {
        $this->_tabs = [];

        foreach ($tabs as $tab) {
            if (\is_array($tab)) {
                $tab = new FieldLayoutTab($tab);
            }

            $tab->setLayout($this);
            $this->_tabs[] = $tab;
        }
    }
}