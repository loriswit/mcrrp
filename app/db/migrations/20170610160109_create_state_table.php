<?php

use Phinx\Migration\AbstractMigration;

class CreateStateTable extends AbstractMigration
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
        $states = $this->table("state");
        $states->addColumn("name", "string");
        $states->addColumn("balance", "biginteger");
        $states->addColumn("initial", "biginteger", ["comment" => "initial balance of registered citizens"]);
        $states->create();
        
        $citizens = $this->table("citizen");
        $citizens->removeColumn("state");
        $citizens->addColumn("state_id", "integer", ["after" => "balance"]);
        $citizens->addForeignKey("state_id", "state", "id");
        $citizens->update();
    }
}
