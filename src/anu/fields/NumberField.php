<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 30.12.2017
 * Time: 20:45
 */

namespace anu\fields;
use Anu;
use anu\base\Model;
use anu\db\Schema;
use anu\models\Field;

class NumberField extends Field{

    /**
     * Returns the default field label when creating new fields
     *
     * @return string
     */
    public static function getFieldLabel():string {
        return Anu::t('anu', 'Number');
    }

    public function getContentColumnType(): string
    {
        return Schema::TYPE_DECIMAL;
    }

    public function normalizeValue($value, Model $element = null)
    {
        return (float) $value;
    }
}