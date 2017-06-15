<?php

use Phinx\Migration\AbstractMigration;

class CreateCitizenTable extends AbstractMigration
{
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
