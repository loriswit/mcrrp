<?php

use Phinx\Migration\AbstractMigration;

class UpdateTransactionTable extends AbstractMigration
{
    public function change()
    {
        $citizens = $this->table("transaction");
        $citizens->addColumn("buyer_state", "boolean",
            ["after" => "buyer_id", "default" => false, "comment" => "true if the buyer is a State"]);
        $citizens->addColumn("seller_state", "boolean",
            ["after" => "seller_id", "default" => false, "comment" => "true if the seller is a State"]);
        $citizens->update();
    }
}
