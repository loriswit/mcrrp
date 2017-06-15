<?php

use Phinx\Migration\AbstractMigration;

class CreateStateTable extends AbstractMigration
{
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
