<?php

use Phinx\Seed\AbstractSeed;

class CitizenSeeder extends AbstractSeed
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
        $this->execute("DELETE FROM citizen; ALTER TABLE citizen AUTO_INCREMENT = 1");
        $this->execute("DELETE FROM state; ALTER TABLE state AUTO_INCREMENT = 1");
        
        $states = $this->table("state");
        $data = [
            [
                "name" => "Toblerone",
                "balance" => 1000000,
                "initial" => 200]
        ];
        $states->insert($data);
        $states->save();
        
        $citizens = $this->table("citizen");
        $data = [
            [
                "code" => "LW12",
                "first_name" => "Loris",
                "last_name" => "Witschard",
                "sex" => "M",
                "state_id" => 1,
                "balance" => 200,
                "player" => "dee3dd3f-42f0-4f45-8d03-99ad769dd6a9"],
        ];
        $citizens->insert($data);
        $citizens->save();
    }
}
