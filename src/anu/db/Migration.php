<?php

namespace anu\db;

use anu\base\Component;
use anu\di\Instance;
use anu\helper\StringHelper;

class Migration extends Component implements MigrationInterface
{
    use SchemaBuilderTrait;

    /**
     * @var Connection|array|string the DB connection object or the application component ID of the DB connection
     * that this migration should work with. Starting from version 2.0.2, this can also be a configuration array
     * for creating the object.
     *
     * Note that when a Migration object is created by the `migrate` command, this property will be overwritten
     * by the command. If you do not want to use the DB connection provided by the command, you may override
     * the [[init()]] method like the following:
     *
     * ```php
     * public function init()
     * {
     *     $this->db = 'db2';
     *     parent::init();
     * }
     * ```
     */
    public $db = 'db';
    /**
     * @var int max number of characters of the SQL outputted. Useful for reduction of long statements and making
     * console output more compact.
     */
    public $maxSqlOutputLength;
    /**
     * @var bool indicates whether the console output should be compacted.
     * If this is set to true, the individual commands ran within the migration will not be output to the console.
     * Default is false, in other words the output is fully verbose by default.
     */
    public $compact = false;


    /**
     * Initializes the migration.
     * This method will set [[db]] to be the 'db' application component, if it is `null`.
     * @throws \anu\base\NotSupportedException
     * @throws \anu\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
        $this->db->getSchema()->refresh();
        $this->db->enableSlaves = false;
    }

    /**
     * @inheritdoc
     */
    protected function getDb()
    {
        return $this->db;
    }

    /**
     * This method contains the logic to be executed when applying this migration.
     * Child classes may override this method to provide actual migration logic.
     * @return bool return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     * @throws Exception
     * @throws Exception
     */
    public function up()
    {
        $transaction = $this->db->beginTransaction();
        try {
            if ($this->safeUp() === false) {
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $this->printException($e);
            $transaction->rollBack();
            return false;
        } catch (\Throwable $e) {
            $this->printException($e);
            $transaction->rollBack();
            return false;
        }

        return null;
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * The default implementation throws an exception indicating the migration cannot be removed.
     * Child classes may override this method if the corresponding migrations can be removed.
     * @return bool return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     * @throws Exception
     * @throws Exception
     */
    public function down()
    {
        $transaction = $this->db->beginTransaction();
        try {
            if ($this->safeDown() === false) {
                $transaction->rollBack();
                return false;
            }
            $transaction->commit();
        } catch (\Exception $e) {
            $this->printException($e);
            $transaction->rollBack();
            return false;
        } catch (\Throwable $e) {
            $this->printException($e);
            $transaction->rollBack();
            return false;
        }

        return null;
    }

    /**
     * @param \Throwable|\Exception $e
     */
    private function printException($e)
    {
        echo 'Exception: ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ")\n";
        echo $e->getTraceAsString() . "\n";
    }

    /**
     * This method contains the logic to be executed when applying this migration.
     * This method differs from [[up()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[up()]] if the DB logic
     * needs to be within a transaction.
     *
     * Note: Not all DBMS support transactions. And some DB queries cannot be put into a transaction. For some examples,
     * please refer to [implicit commit](http://dev.mysql.com/doc/refman/5.7/en/implicit-commit.html).
     *
     * @return bool return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeUp()
    {
    }

    /**
     * This method contains the logic to be executed when removing this migration.
     * This method differs from [[down()]] in that the DB logic implemented here will
     * be enclosed within a DB transaction.
     * Child classes may implement this method instead of [[down()]] if the DB logic
     * needs to be within a transaction.
     *
     * Note: Not all DBMS support transactions. And some DB queries cannot be put into a transaction. For some examples,
     * please refer to [implicit commit](http://dev.mysql.com/doc/refman/5.7/en/implicit-commit.html).
     *
     * @return bool return a false value to indicate the migration fails
     * and should not proceed further. All other return values mean the migration succeeds.
     */
    public function safeDown()
    {
    }

    /**
     * Executes a SQL statement.
     * This method executes the specified SQL statement using [[db]].
     * @param string $sql the SQL statement to be executed
     * @param array $params input parameters (name => value) for the SQL execution.
     * See [[Command::execute()]] for more details.
     * @throws \anu\base\Exception
     */
    public function execute($sql, $params = [])
    {
        $sqlOutput = $sql;
        if ($this->maxSqlOutputLength !== null) {
            $sqlOutput = StringHelper::truncate($sql, $this->maxSqlOutputLength, '[... hidden]');
        }

        $this->db->createCommand($sql)->bindValues($params)->execute();
    }

    /**
     * Creates and executes an INSERT SQL statement.
     * The method will properly escape the column names, and bind the values to be inserted.
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column data (name => value) to be inserted into the table.
     * @throws \anu\base\Exception
     */
    public function insert($table, $columns)
    {
        $this->db->createCommand()->insert($table, $columns)->execute();
    }

    /**
     * Creates and executes a batch INSERT SQL statement.
     * The method will properly escape the column names, and bind the values to be inserted.
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column names.
     * @param array $rows the rows to be batch inserted into the table
     * @throws \anu\base\Exception
     */
    public function batchInsert($table, $columns, $rows)
    {
        $this->db->createCommand()->batchInsert($table, $columns, $rows)->execute();
    }

    /**
     * Creates and executes an UPDATE SQL statement.
     * The method will properly escape the column names and bind the values to be updated.
     * @param string $table the table to be updated.
     * @param array $columns the column data (name => value) to be updated.
     * @param array|string $condition the conditions that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify conditions.
     * @param array $params the parameters to be bound to the query.
     * @throws \anu\base\Exception
     */
    public function update($table, $columns, $condition = '', $params = [])
    {
        $this->db->createCommand()->update($table, $columns, $condition, $params)->execute();
    }

    /**
     * Creates and executes a DELETE SQL statement.
     * @param string $table the table where the data will be deleted from.
     * @param array|string $condition the conditions that will be put in the WHERE part. Please
     * refer to [[Query::where()]] on how to specify conditions.
     * @param array $params the parameters to be bound to the query.
     * @throws \anu\base\Exception
     */
    public function delete($table, $condition = '', $params = [])
    {
        $this->db->createCommand()->delete($table, $condition, $params)->execute();
    }

    /**
     * Builds and executes a SQL statement for creating a new DB table.
     *
     * The columns in the new  table should be specified as name-definition pairs (e.g. 'name' => 'string'),
     * where name stands for a column name which will be properly quoted by the method, and definition
     * stands for the column type which can contain an abstract DB type.
     *
     * The [[QueryBuilder::getColumnType()]] method will be invoked to convert any abstract type into a physical one.
     *
     * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
     * put into the generated SQL.
     *
     * @param string $table the name of the table to be created. The name will be properly quoted by the method.
     * @param array $columns the columns (name => definition) in the new table.
     * @param string $options additional SQL fragment that will be appended to the generated SQL.
     * @throws \anu\base\Exception
     * @throws \anu\base\Exception
     */
    public function createTable($table, $columns, $options = null)
    {
        $this->db->createCommand()->createTable($table, $columns, $options)->execute();
        foreach ($columns as $column => $type) {
            if ($type instanceof ColumnSchemaBuilder && $type->comment !== null) {
                $this->db->createCommand()->addCommentOnColumn($table, $column, $type->comment)->execute();
            }
        }
    }

    /**
     * Builds and executes a SQL statement for renaming a DB table.
     * @param string $table the table to be renamed. The name will be properly quoted by the method.
     * @param string $newName the new table name. The name will be properly quoted by the method.
     * @throws \anu\base\Exception
     */
    public function renameTable($table, $newName)
    {
        $this->db->createCommand()->renameTable($table, $newName)->execute();
    }

    /**
     * Builds and executes a SQL statement for dropping a DB table.
     * @param string $table the table to be dropped. The name will be properly quoted by the method.
     * @throws \anu\base\Exception
     */
    public function dropTable($table)
    {
        $this->db->createCommand()->dropTable($table)->execute();
    }

    /**
     * Builds and executes a SQL statement for truncating a DB table.
     * @param string $table the table to be truncated. The name will be properly quoted by the method.
     * @throws \anu\base\Exception
     */
    public function truncateTable($table)
    {
        $this->db->createCommand()->truncateTable($table)->execute();
    }

    /**
     * Builds and executes a SQL statement for adding a new DB column.
     * @param string $table the table that the new column will be added to. The table name will be properly quoted by the method.
     * @param string $column the name of the new column. The name will be properly quoted by the method.
     * @param string $type the column type. The [[QueryBuilder::getColumnType()]] method will be invoked to convert abstract column type (if any)
     * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
     * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
     * @throws \anu\base\Exception
     * @throws \anu\base\Exception
     */
    public function addColumn($table, $column, $type)
    {
        $this->db->createCommand()->addColumn($table, $column, $type)->execute();
        if ($type instanceof ColumnSchemaBuilder && $type->comment !== null) {
            $this->db->createCommand()->addCommentOnColumn($table, $column, $type->comment)->execute();
        }
    }

    /**
     * Builds and executes a SQL statement for dropping a DB column.
     * @param string $table the table whose column is to be dropped. The name will be properly quoted by the method.
     * @param string $column the name of the column to be dropped. The name will be properly quoted by the method.
     * @throws \anu\base\Exception
     */
    public function dropColumn($table, $column)
    {
        $this->db->createCommand()->dropColumn($table, $column)->execute();
    }

    /**
     * Builds and executes a SQL statement for renaming a column.
     * @param string $table the table whose column is to be renamed. The name will be properly quoted by the method.
     * @param string $name the old name of the column. The name will be properly quoted by the method.
     * @param string $newName the new name of the column. The name will be properly quoted by the method.
     * @throws \anu\base\Exception
     */
    public function renameColumn($table, $name, $newName)
    {
        $this->db->createCommand()->renameColumn($table, $name, $newName)->execute();
    }

    /**
     * Builds and executes a SQL statement for changing the definition of a column.
     * @param string $table the table whose column is to be changed. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be changed. The name will be properly quoted by the method.
     * @param string $type the new column type. The [[QueryBuilder::getColumnType()]] method will be invoked to convert abstract column type (if any)
     * into the physical one. Anything that is not recognized as abstract type will be kept in the generated SQL.
     * For example, 'string' will be turned into 'varchar(255)', while 'string not null' will become 'varchar(255) not null'.
     * @throws \anu\base\Exception
     * @throws \anu\base\Exception
     */
    public function alterColumn($table, $column, $type)
    {
       $this->db->createCommand()->alterColumn($table, $column, $type)->execute();
        if ($type instanceof ColumnSchemaBuilder && $type->comment !== null) {
            $this->db->createCommand()->addCommentOnColumn($table, $column, $type->comment)->execute();
        }

    }

    /**
     * Builds and executes a SQL statement for creating a primary key.
     * The method will properly quote the table and column names.
     * @param string $name the name of the primary key constraint.
     * @param string $table the table that the primary key constraint will be added to.
     * @param string|array $columns comma separated string or array of columns that the primary key will consist of.
     * @throws \anu\base\Exception
     */
    public function addPrimaryKey($name, $table, $columns)
    {
       $this->db->createCommand()->addPrimaryKey($name, $table, $columns)->execute();
    }

    /**
     * Builds and executes a SQL statement for dropping a primary key.
     * @param string $name the name of the primary key constraint to be removed.
     * @param string $table the table that the primary key constraint will be removed from.
     * @throws \anu\base\Exception
     */
    public function dropPrimaryKey($name, $table)
    {
        $time = $this->beginCommand("drop primary key $name");
        $this->db->createCommand()->dropPrimaryKey($name, $table)->execute();
        $this->endCommand($time);
    }

    /**
     * Builds a SQL statement for adding a foreign key constraint to an existing table.
     * The method will properly quote the table and column names.
     * @param string $name the name of the foreign key constraint.
     * @param string $table the table that the foreign key constraint will be added to.
     * @param string|array $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas or use an array.
     * @param string $refTable the table that the foreign key references to.
     * @param string|array $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas or use an array.
     * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @throws \anu\base\Exception
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        if ($name === null) {
            $name = $this->db->getForeignKeyName($table, $columns);
        }
        $this->db->createCommand()->addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update)->execute();
    }

    /**
     * Builds a SQL statement for dropping a foreign key constraint.
     * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
     * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
     * @throws \anu\base\Exception
     */
    public function dropForeignKey($name, $table)
    {
        $this->db->createCommand()->dropForeignKey($name, $table)->execute();
    }

    /**
     * Builds and executes a SQL statement for creating a new index.
     * @param string $name the name of the index. The name will be properly quoted by the method.
     * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
     * @param string|array $columns the column(s) that should be included in the index. If there are multiple columns, please separate them
     * by commas or use an array. Each column name will be properly quoted by the method. Quoting will be skipped for column names that
     * include a left parenthesis "(".
     * @param bool $unique whether to add UNIQUE constraint on the created index.
     * @throws \anu\base\Exception
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
        if ($name === null) {
            $name = $this->db->getIndexName($table, $columns, $unique);
        }
         $this->db->createCommand()->createIndex($name, $table, $columns, $unique)->execute();
    }

    /**
     * Builds and executes a SQL statement for dropping an index.
     * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
     * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
     * @throws \anu\base\Exception
     */
    public function dropIndex($name, $table)
    {
        $this->db->createCommand()->dropIndex($name, $table)->execute();
    }

    /**
     * Builds and execute a SQL statement for adding comment to column.
     *
     * @param string $table the table whose column is to be commented. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be commented. The column name will be properly quoted by the method.
     * @param string $comment the text of the comment to be added. The comment will be properly quoted by the method.
     * @throws \anu\base\Exception
     */
    public function addCommentOnColumn($table, $column, $comment)
    {
        $this->db->createCommand()->addCommentOnColumn($table, $column, $comment)->execute();
    }

    /**
     * Builds a SQL statement for adding comment to table.
     *
     * @param string $table the table whose column is to be commented. The table name will be properly quoted by the method.
     * @param string $comment the text of the comment to be added. The comment will be properly quoted by the method.
     * @throws \anu\base\Exception
     */
    public function addCommentOnTable($table, $comment)
    {
        $this->db->createCommand()->addCommentOnTable($table, $comment)->execute();
    }

    /**
     * Builds and execute a SQL statement for dropping comment from column.
     *
     * @param string $table the table whose column is to be commented. The table name will be properly quoted by the method.
     * @param string $column the name of the column to be commented. The column name will be properly quoted by the method.
     * @throws \anu\base\Exception
     */
    public function dropCommentFromColumn($table, $column)
    {
        $this->db->createCommand()->dropCommentFromColumn($table, $column)->execute();
    }

    /**
     * Builds a SQL statement for dropping comment from table.
     *
     * @param string $table the table whose column is to be commented. The table name will be properly quoted by the method.
     * @throws \anu\base\Exception
     */
    public function dropCommentFromTable($table)
    {
        $this->db->createCommand()->dropCommentFromTable($table)->execute();
    }
}
