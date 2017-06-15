<?php

use Phinx\Seed\AbstractSeed;

class StateSeeder extends AbstractSeed
{
    public function run()
    {
        $this->execute("DELETE FROM state; ALTER TABLE state AUTO_INCREMENT = 1");
    
        $table = $this->table("state");
        $data = [
            [
                "name" => "Toblerone",
                "balance" => 1000000,
                "initial" => 100],
            [
                "name" => "Cailler",
                "balance" => 1000000,
                "initial" => 100]
        ];
        $table->insert($data);
        $table->save();
    }
}
