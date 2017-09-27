<?php

use Phinx\Migration\AbstractMigration;

class AddStateCurrency extends AbstractMigration
{
    public function change()
    {
        $table = $this->table("state");
        $table->addColumn("currency", "string", ["after" => "name", "default" => "$"]);
        $table->update();
    }
}
