<?php

use Phinx\Migration\AbstractMigration;

class UpdateTransactionTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table("transaction");
        $table->addColumn("buyer_state", "boolean",
            ["after" => "buyer_id", "default" => false, "comment" => "true if the buyer is a State"]);
        $table->addColumn("seller_state", "boolean",
            ["after" => "seller_id", "default" => false, "comment" => "true if the seller is a State"]);
        $table->update();
    }
}
