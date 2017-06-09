<?php

use Phinx\Migration\AbstractMigration;

class CreateCitizenTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table("citizen");
        $table->addColumn("code", "string");
        $table->addColumn("first_name", "string");
        $table->addColumn("last_name", "string");
        $table->addColumn("sex", "string");
        $table->addColumn("state", "string");
        $table->addColumn("balance", "biginteger");
        $table->addColumn("player", "uuid");
        $table->create();
    }
}
