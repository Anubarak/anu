<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 30.12.2017
 * Time: 21:55
 */

namespace anu\service;

use anu\base\Component;

class Content extends Component{

    /**
     * @var string Table for all contentrecords
     */
    public $contentTable = '{{%content}}';

    /**
     * @var string Field Column prefix
     */
    public $fieldColumnPrefix = 'field_';
}