<?php
/**
 * Created by PhpStorm.
 * User: scham
 * Date: 04.01.2018
 * Time: 12:02
 */

namespace anu\models;


use anu\base\InvalidConfigException;
use anu\base\Model;
use Anu;

class FieldLayoutTab extends Model
{
    // Properties
    // =========================================================================

    /**
     * @var int|null ID
     */
    public $id;
    /**
     * @var int|null Layout ID
     */
    public $layoutId;
    /**
     * @var string|null Name
     */
    public $name;
    /**
     * @var int|null Sort order
     */
    public $sortOrder;
    /**
     * @var FieldLayout|null
     */
    private $_layout;
    /**
     * @var \anu\models\Field[]|null
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
            [['id', 'layoutId'], 'number', 'integerOnly' => true],
            [['name'], 'string', 'max' => 255],
            [['sortOrder'], 'string', 'max' => 4],
        ];
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
                'fields' => $this->getFields()
            ]
        );
    }

    /**
     * Returns the tab’s fields.
     *
     * @return \anu\models\Field[] The tab’s fields.
     * @throws \anu\base\InvalidConfigException
     */
    public function getFields(): array
    {
        if ($this->_fields !== null) {
            return $this->_fields;
        }

        $this->_fields = [];

        if ($layout = $this->getLayout()) {
            foreach ($layout->getFields() as $field) {
                /** @var Field $field */
                if ($field->tabId == $this->id) {
                    $this->_fields[] = $field;
                }
            }
        }

        return $this->_fields;
    }

    /**
     * Sets the tab’s fields.
     *
     * @param \anu\models\Field[] $fields The tab’s fields.
     *
     * @return void
     */
    public function setFields(array $fields): void
    {
        $this->_fields = $fields;
    }

    /**
     * Returns the tab’s layout.
     *
     * @return FieldLayout|null The tab’s layout.
     * @throws InvalidConfigException if [[groupId]] is set but invalid
     */
    public function getLayout(): ?FieldLayout
    {
        if ($this->_layout !== null) {
            return $this->_layout;
        }

        if (!$this->layoutId) {
            return null;
        }

        if (($this->_layout = Anu::$app->getFields()->getLayoutById($this->layoutId)) === null) {
            throw new InvalidConfigException('Invalid layout ID: ' . $this->layoutId);
        }

        return $this->_layout;
    }

    /**
     * Sets the tab’s layout.
     *
     * @param FieldLayout $layout The tab’s layout.
     *
     * @return void
     */
    public function setLayout(FieldLayout $layout): void
    {
        $this->_layout = $layout;
    }
}