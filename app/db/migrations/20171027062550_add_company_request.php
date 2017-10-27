<?php

use Phinx\Migration\AbstractMigration;

class AddCompanyRequest extends AbstractMigration
{
    public function change()
    {
        $table = $this->table("company");
        $table->addColumn("request", "boolean", ["after" => "bank", "default" => true]);
        $table->update();
    }
}
