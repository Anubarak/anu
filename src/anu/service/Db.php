<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 23.12.2017
 * Time: 19:51
 */

namespace anu\service;

use anu\base\Component;
use Pixie\QueryBuilder\QueryBuilderHandler;

class Db extends Component{

    private $connection;

    public function createCommand(){
        $connection  = $this->connection? : $this->createConnection();
        return new QueryBuilderHandler($connection);
    }

    /**
     * @return \Pixie\Connection
     */
    public function createConnection(){
        $dbConfig = \Anu::$app->config->getDb();
        $this->connection = new \Pixie\Connection('mysql', $dbConfig);

        return $this->connection;
    }

}