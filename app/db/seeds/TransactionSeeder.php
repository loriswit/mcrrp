<?php

use Phinx\Seed\AbstractSeed;

class TransactionSeeder extends AbstractSeed
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
        $this->execute("DELETE FROM transaction; ALTER TABLE transaction AUTO_INCREMENT = 1");
        
        $table = $this->table("transaction");
        $data = [
            [
                "amount" => 20,
                "description" => "bought 10 apples",
                "buyer_id" => 1,
                "seller_id" => 2,
                "timestamp" => 1497209532],
            [
                "amount" => 15,
                "description" => "bought 5 breads",
                "buyer_id" => 2,
                "seller_id" => 1,
                "timestamp" => 1491857283],
            [
                "amount" => 30,
                "description" => "gift",
                "buyer_id" => 1,
                "seller_id" => 3,
                "timestamp" => 1497467364],
            [
                "amount" => 10,
                "description" => "bought 2 bricks",
                "buyer_id" => 1,
                "seller_id" => 3,
                "timestamp" => 1492438517],
            [
                "amount" => 40,
                "description" => "bought 12 birch logs",
                "buyer_id" => 3,
                "seller_id" => 1,
                "timestamp" => 1493826473],
            [
                "amount" => 50,
                "description" => "loan",
                "buyer_id" => 2,
                "seller_id" => 3,
                "timestamp" => 1497209532],
            [
                "amount" => 100,
                "description" => "bought 1 pickaxe",
                "buyer_id" => 3,
                "seller_id" => 2,
                "timestamp" => 1497859371]
        ];
        $table->insert($data);
        $table->save();
    }
}
