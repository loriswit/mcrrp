<?php

use Phinx\Migration\AbstractMigration;

class CreateMessageTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table("message");
        $table->addColumn("body", "text");
        $table->addColumn("sender_id", "integer");
        $table->addColumn("receiver_id", "integer");
        $table->addColumn("timestamp", "integer");
        $table->addForeignKey("sender_id", "citizen", "id");
        $table->addForeignKey("receiver_id", "citizen", "id");
        $table->create();
    }
}
