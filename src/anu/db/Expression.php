<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace anu\db;

/**
 * Expression represents a DB expression that does not need escaping or quoting.
 *
 * ```php
 * $expression = new Expression('NOW()');
 * $now = (new \anu\db\Query)->select($expression)->scalar();  // SELECT NOW();
 * echo $now; // prints the current date
 * ```
 *
 * @author Robin Schambach
 */
class Expression extends \anu\base\BaseObject
{
    /**
     * @var string the DB expression
     */
    public $expression;
    /**
     * @var array list of parameters that should be bound for this expression.
     * The keys are placeholders appearing in [[expression]] and the values
     * are the corresponding parameter values.
     */
    public $params = [];


    /**
     * Constructor.
     * @param string $expression the DB expression
     * @param array $params parameters
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($expression, $params = [], $config = [])
    {
        $this->expression = $expression;
        $this->params = $params;
        parent::__construct($config);
    }

    /**
     * String magic method.
     * @return string the DB expression
     */
    public function __toString()
    {
        return $this->expression;
    }
}
