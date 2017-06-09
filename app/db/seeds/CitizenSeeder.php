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
        
        $table = $this->table("citizen");
        
        $data = [
            [
                "code" => "LW12",
                "first_name" => "Loris",
                "last_name" => "Witschard",
                "sex" => "M",
                "state" => "Toblerone",
                "balance" => 200,
                "player" => "dee3dd3f-42f0-4f45-8d03-99ad769dd6a9"],
            [
                "code" => "JH34",
                "first_name" => "Jan",
                "last_name" => "Hamza",
                "sex" => "M",
                "state" => "Milka",
                "balance" => 300,
                "player" => "4064e17d-3f2e-4eb5-8e1a-d134668b11cc"]
        
        ];
        
        $table->insert($data);
        $table->save();
    }
}
