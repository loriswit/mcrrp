<?php

use Phinx\Migration\AbstractMigration;

class CreateLockTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table("lock");
        $table->addColumn("owner_id", "integer");
        $table->addColumn("name", "string");
        $table->addColumn("type", "string");
        $table->addColumn("x", "integer");
        $table->addColumn("y", "integer");
        $table->addColumn("z", "integer");
        $table->addForeignKey("owner_id", "citizen", "id");
        $table->create();
        
        $table = $this->table("authorized");
        $table->addColumn("lock_id", "integer");
        $table->addColumn("citizen_id", "integer");
        $table->addForeignKey("lock_id", "lock", "id");
        $table->addForeignKey("citizen_id", "citizen", "id");
        $table->create();
    }
}
