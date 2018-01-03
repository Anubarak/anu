<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 25.12.2017
 * Time: 12:39
 */

namespace anu\elements\db;

use anu\base\Element;
use anu\db\Connection;
use anu\db\QueryInterface;

interface ElementQueryInterface extends QueryInterface{

    /**
     * Executes the query and returns all results as an array.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return Element[] the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all($db = null);

    /**
     * Executes the query and returns a single row of result.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return Element|bool the first row (in terms of an array) of the query result. False is returned if the query
     * results in nothing.
     */
    public function one($db = null);
    
}