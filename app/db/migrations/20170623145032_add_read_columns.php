<?php

use Phinx\Migration\AbstractMigration;

class AddReadColumns extends AbstractMigration
{
    public function change()
    {
        $transactions = $this->table("transaction");
        $transactions->addColumn("seen", "integer",
            ["after" => "timestamp", "default" => 0]);
        $transactions->update();
    
        $messages = $this->table("message");
        $messages->addColumn("seen", "integer",
            ["after" => "timestamp", "default" => 0]);
        $messages->update();
    }
}
