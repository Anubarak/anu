<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 31.12.2017
 * Time: 13:09
 */

namespace anu\base;

abstract class SavableComponent extends Model{
    use SavableComponentTrait;

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
}