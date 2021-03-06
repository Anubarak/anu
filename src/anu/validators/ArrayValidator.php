<?php
/**
 * Created by PhpStorm.
 * User: scham
 * Date: 12.01.2018
 * Time: 09:00
 */

namespace anu\validators;

use Anu;

class ArrayValidator extends Validator
{
    // Public Methods
    // =========================================================================

    /**
     * @var int|array|null specifies the count limit of the value to be validated.
     * This can be specified in one of the following forms:
     *
     * - an int: the exact count that the value should be of;
     * - an array of one element: the minimum count that the value should be of. For example, `[8]`.
     *   This will overwrite [[min]].
     * - an array of two elements: the minimum and maximum counts that the value should be of.
     *   For example, `[8, 128]`. This will overwrite both [[min]] and [[max]].
     * @see tooFew for the customized message for a too short array.
     * @see tooMany for the customized message for a too long array.
     * @see notEqual for the customized message for an array that does not match desired count.
     */
    public $count;
    /**
     * @var int|null maximum count. If not set, it means no maximum count limit.
     * @see tooMany for the customized message for a too long array.
     */
    public $max;
    /**
     * @var int|null minimum count. If not set, it means no minimum count limit.
     * @see tooFew for the customized message for a too short array.
     */
    public $min;
    /**
     * @var string|null user-defined error message used when the count of the value is smaller than [[min]].
     */
    public $tooFew;
    /**
     * @var string|null user-defined error message used when the count of the value is greater than [[max]].
     */
    public $tooMany;
    /**
     * @var string|null user-defined error message used when the count of the value is not equal to [[count]].
     */
    public $notEqual;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (\is_array($this->count)) {
            if (isset($this->count[0])) {
                $this->min = $this->count[0];
            }

            if (isset($this->count[1])) {
                $this->max = $this->count[1];
            }

            $this->count = null;
        }

        if ($this->message === null) {
            $this->message = Anu::t('app', '{attribute} must be an array.');
        }

        if ($this->min !== null && $this->tooFew === null) {
            $this->tooFew = Anu::t('app', '{attribute} should contain at least {min, number} {min, plural, one{item} other{items}}.');
        }

        if ($this->max !== null && $this->tooMany === null) {
            $this->tooMany = Anu::t('app', '{attribute} should contain at most {max, number} {max, plural, one{item} other{items}}.');
        }

        if ($this->count !== null && $this->notEqual === null) {
            $this->notEqual = Anu::t('app', '{attribute} should contain {count, number} {count, plural, one{item} other{items}}.');
        }
    }

    /**
     * @inheritdoc
     */
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if (!$value instanceof \Countable && !\is_array($value)) {
            $this->addError($model, $attribute, $this->message);

            return;
        }

        $count = \count($value);

        if ($this->min !== null && $count < $this->min) {
            $this->addError($model, $attribute, $this->tooFew, ['min' => $this->min]);
        }
        if ($this->max !== null && $count > $this->max) {
            $this->addError($model, $attribute, $this->tooMany, ['max' => $this->max]);
        }
        if ($this->count !== null && $count !== $this->count) {
            $this->addError($model, $attribute, $this->notEqual, ['count' => $this->count]);
        }
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        if (!\is_string($value)) {
            return [$this->message, []];
        }

        $count = \count((array) $value);

        if ($this->min !== null && $count < $this->min) {
            return [$this->tooFew, ['min' => $this->min]];
        }
        if ($this->max !== null && $count > $this->max) {
            return [$this->tooMany, ['max' => $this->max]];
        }
        if ($this->count !== null && $count !== $this->count) {
            return [$this->notEqual, ['count' => $this->count]];
        }

        return null;
    }
}
