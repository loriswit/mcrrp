<?php

use Phinx\Migration\AbstractMigration;

class CreateTransactionTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table("transaction");
        $table->addColumn("amount", "biginteger");
        $table->addColumn("description", "text");
        $table->addColumn("buyer_id", "integer");
        $table->addColumn("seller_id", "integer");
        $table->addColumn("timestamp", "integer");
        $table->addForeignKey("buyer_id", "citizen", "id");
        $table->addForeignKey("seller_id", "citizen", "id");
        $table->create();
    }
}
