<?php
/**
 * Created by PhpStorm.
 * User: anuba
 * Date: 24.12.2017
 * Time: 18:03
 */

namespace anu\migrations;

use anu\db\Migration;
use anu\db\Schema;

class Install extends Migration{

    /**
     * @return bool|void
     * @throws \anu\base\Exception
     */
    public function up(){
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();
        $this->insertDefaultData();
    }


    /**
     * @throws \anu\base\Exception
     */
    private function createTables(){
        $this->createTable('{{%assets}}', [
            'id' => $this->integer()->notNull(),
            'filename' => $this->string()->notNull(),
            'kind' => $this->string(50)->notNull()->defaultValue('unknown'),
            'width' => $this->integer()->unsigned(),
            'height' => $this->integer()->unsigned(),
            'size' => $this->bigInteger()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);

        $this->createTable('{{%categories}}', [
            'id' => $this->integer()->notNull(),
            'groupId' => $this->integer()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);
        $this->createTable('{{%categorygroups}}', [
            'id' => $this->primaryKey(),
            'fieldLayoutId' => $this->integer(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createTable('{{%content}}', [
            'id' => $this->primaryKey(),
            'elementId' => $this->integer()->notNull(),
            'title' => $this->string(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createTable('{{%elements}}', [
            'id' => $this->primaryKey(),
            'fieldLayoutId' => $this->integer(),
            'type' => $this->string()->notNull(),
            'enabled' => $this->boolean()->defaultValue(true)->notNull(),
            'archived' => $this->boolean()->defaultValue(false)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createTable('{{%entries}}', [
            'id' => $this->integer()->notNull(),
            'sectionId' => $this->integer()->notNull(),
            'typeId' => $this->integer()->notNull(),
            'authorId' => $this->integer(),
            'postDate' => $this->dateTime(),
            'expiryDate' => $this->dateTime(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);
        $this->createTable('{{%entrytypes}}', [
            'id' => $this->primaryKey(),
            'sectionId' => $this->integer()->notNull(),
            'fieldLayoutId' => $this->integer(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'hasTitleField' => $this->boolean()->defaultValue(true)->notNull(),
            'titleLabel' => $this->string()->defaultValue('Title'),
            'titleFormat' => $this->string(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createTable('{{%fieldgroups}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createTable('{{%fieldlayoutfields}}', [
            'id' => $this->primaryKey(),
            'layoutId' => $this->integer()->notNull(),
            'tabId' => $this->integer()->notNull(),
            'fieldId' => $this->integer()->notNull(),
            'required' => $this->boolean()->defaultValue(false)->notNull(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createTable('{{%fieldlayouts}}', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createTable('{{%fieldlayouttabs}}', [
            'id' => $this->primaryKey(),
            'layoutId' => $this->integer()->notNull(),
            'name' => $this->string()->notNull(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createTable('{{%fields}}', [
            'id' => $this->primaryKey(),
            'groupId' => $this->integer(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string(64)->notNull(),
            'context' => $this->string()->notNull()->defaultValue('global'),
            'instructions' => $this->text(),
            'translationMethod' => $this->string()->notNull()->defaultValue('none'),
            'translationKeyFormat' => $this->text(),
            'type' => $this->string()->notNull(),
            'settings' => $this->text(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createTable('{{%relations}}', [
            'id' => $this->primaryKey(),
            'fieldId' => $this->integer()->notNull(),
            'sourceId' => $this->integer()->notNull(),
            'targetId' => $this->integer()->notNull(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createTable('{{%sections}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'handle' => $this->string()->notNull(),
            'type' => $this->enum(['"single"', '"channel"', '"structure"'])->notNull()->defaultValue('channel'),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createTable('{{%userpermissions}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);
        $this->createTable('{{%users}}', [
            'id' => $this->integer()->notNull(),
            'username' => $this->string(100)->notNull(),
            'photoId' => $this->integer(),
            'firstName' => $this->string(100),
            'lastName' => $this->string(100),
            'email' => $this->string()->notNull(),
            'password' => $this->string(),
            'admin' => $this->boolean()->defaultValue(false)->notNull(),
            'client' => $this->boolean()->defaultValue(false)->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
            'PRIMARY KEY(id)',
        ]);
    }

    /**
     * @throws \anu\base\Exception
     */
    private function createIndexes(){
        $this->createIndex(null, '{{%categories}}', ['groupId'], false);
        $this->createIndex(null, '{{%categorygroups}}', ['name'], true);
        $this->createIndex(null, '{{%categorygroups}}', ['handle'], true);
        $this->createIndex(null, '{{%categorygroups}}', ['fieldLayoutId'], false);
        $this->createIndex(null, '{{%content}}', ['title'], false);
        $this->createIndex(null, '{{%elements}}', ['fieldLayoutId'], false);
        $this->createIndex(null, '{{%elements}}', ['type'], false);
        $this->createIndex(null, '{{%elements}}', ['enabled'], false);
        $this->createIndex(null, '{{%elements}}', ['archived', 'dateCreated'], false);
        $this->createIndex(null, '{{%entries}}', ['postDate'], false);
        $this->createIndex(null, '{{%entries}}', ['expiryDate'], false);
        $this->createIndex(null, '{{%entries}}', ['authorId'], false);
        $this->createIndex(null, '{{%entries}}', ['sectionId'], false);
        $this->createIndex(null, '{{%entries}}', ['typeId'], false);
        $this->createIndex(null, '{{%fieldgroups}}', ['name'], true);
        $this->createIndex(null, '{{%fieldlayoutfields}}', ['layoutId', 'fieldId'], true);
        $this->createIndex(null, '{{%fieldlayoutfields}}', ['sortOrder'], false);
        $this->createIndex(null, '{{%fieldlayoutfields}}', ['tabId'], false);
        $this->createIndex(null, '{{%fieldlayoutfields}}', ['fieldId'], false);
        $this->createIndex(null, '{{%fieldlayouts}}', ['type'], false);
        $this->createIndex(null, '{{%fieldlayouttabs}}', ['sortOrder'], false);
        $this->createIndex(null, '{{%fieldlayouttabs}}', ['layoutId'], false);
        $this->createIndex(null, '{{%fields}}', ['handle', 'context'], true);
        $this->createIndex(null, '{{%fields}}', ['groupId'], false);
        $this->createIndex(null, '{{%fields}}', ['context'], false);
        $this->createIndex(null, '{{%relations}}', ['fieldId', 'sourceId', 'targetId'], true);
        $this->createIndex(null, '{{%relations}}', ['sourceId'], false);
        $this->createIndex(null, '{{%relations}}', ['targetId'], false);
        $this->createIndex(null, '{{%sections}}', ['handle'], true);
        $this->createIndex(null, '{{%sections}}', ['name'], true);
        $this->createIndex(null, '{{%userpermissions}}', ['name'], true);
        $this->createIndex(null, '{{%users}}', ['username'], true);
        $this->createIndex(null, '{{%users}}', ['email'], true);
        $this->createIndex(null, '{{%users}}', ['uid'], false);
    }

    /**
     * Adds the foreign keys.
     *
     * @return void
     * @throws \anu\base\Exception
     */
    protected function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%categories}}', ['groupId'], '{{%categorygroups}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%categories}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%categorygroups}}', ['fieldLayoutId'], '{{%fieldlayouts}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%content}}', ['elementId'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%elements}}', ['fieldLayoutId'], '{{%fieldlayouts}}', ['id'], 'SET NULL', null);
        $this->addForeignKey(null, '{{%entries}}', ['authorId'], '{{%users}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%entries}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%entries}}', ['sectionId'], '{{%sections}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%entries}}', ['typeId'], '{{%entrytypes}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%fieldlayoutfields}}', ['fieldId'], '{{%fields}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%fieldlayoutfields}}', ['layoutId'], '{{%fieldlayouts}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%fieldlayoutfields}}', ['tabId'], '{{%fieldlayouttabs}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%fieldlayouttabs}}', ['layoutId'], '{{%fieldlayouts}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%fields}}', ['groupId'], '{{%fieldgroups}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%relations}}', ['fieldId'], '{{%fields}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%relations}}', ['sourceId'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%relations}}', ['targetId'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%users}}', ['id'], '{{%elements}}', ['id'], 'CASCADE', null);
        $this->addForeignKey(null, '{{%users}}', ['photoId'], '{{%assets}}', ['id'], 'SET NULL', null);
    }

    private function insertDefaultData(){

    }
}