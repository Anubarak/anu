<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 30.12.2017
 * Time: 20:45
 */

namespace anu\fields;
use Anu;
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
}