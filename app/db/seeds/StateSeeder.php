<?php

use Phinx\Seed\AbstractSeed;

class StateSeeder extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
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
