<?php

use Phinx\Seed\AbstractSeed;

class MessageSeeder extends AbstractSeed
{
    public function run()
    {
        $this->execute("DELETE FROM message; ALTER TABLE message AUTO_INCREMENT = 1");
    
        $table = $this->table("message");
        $data = [
            [
                "body" => "hello",
                "sender_id" => "1",
                "receiver_id" => "2",
                "timestamp" => "1497790317"],
            [
                "body" => "I need stones",
                "sender_id" => "1",
                "receiver_id" => "2",
                "timestamp" => "1497790322"],
            [
                "body" => "I have no more",
                "sender_id" => "2",
                "receiver_id" => "1",
                "timestamp" => "1497790687"],
            [
                "body" => "no problem",
                "sender_id" => "1",
                "receiver_id" => "2",
                "timestamp" => "1497790899"],
            [
                "body" => "hello there",
                "sender_id" => "1",
                "receiver_id" => "3",
                "timestamp" => "1497787182"],
        ];
        $table->insert($data);
        $table->save();
    }
}
