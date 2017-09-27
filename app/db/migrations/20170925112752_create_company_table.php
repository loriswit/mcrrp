<?php

use Phinx\Migration\AbstractMigration;

class CreateCompanyTable extends AbstractMigration
{
    public function change()
    {
        $table = $this->table("company");
        $table->addColumn("name", "string");
        $table->addColumn("description", "string");
        $table->addColumn("profession", "string");
        $table->addColumn("presentation", "text");
        $table->addColumn("state_id", "integer");
        $table->addColumn("founder_id", "integer");
        $table->addColumn("government", "boolean", ["default" => false]);
        $table->addColumn("press", "boolean", ["default" => false]);
        $table->addColumn("bank", "boolean", ["default" => false]);
        $table->addColumn("founded", "integer");
        $table->addColumn("closed", "integer", ["default" => 0]);
        $table->addForeignKey("state_id", "state", "id");
        $table->addForeignKey("founder_id", "citizen", "id");
        $table->create();
        
        $table = $this->table("craft");
        $table->addColumn("company_id", "integer");
        $table->addColumn("material", "string");
        $table->addForeignKey("company_id", "company", "id");
        $table->create();
        
        $table = $this->table("worker");
        $table->addColumn("company_id", "integer");
        $table->addColumn("citizen_id", "integer");
        $table->addColumn("leader", "boolean", ["default" => false]);
        $table->addColumn("hired", "integer");
        $table->addColumn("dismissed", "integer", ["default" => 0]);
        $table->addForeignKey("company_id", "company", "id");
        $table->addForeignKey("citizen_id", "citizen", "id");
        $table->create();
    }
}
