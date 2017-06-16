<?php

class Login extends Page
{
    protected $visitorOnly = true;
    
    protected function title()
    {
        return "Log in";
    }
    
    protected function run()
    {
    }
    
    protected function submit()
    {
        $name = $_POST["username"];
    
        $json = @file_get_contents("https://api.mojang.com/users/profiles/minecraft/$name");
    
        if($json === false || empty($json))
            throw new InvalidInputException("Invalid username. Please try again.");
    
        $data = json_decode($json, true);
        $uuid = $data["id"];
        $uuid = substr_replace($uuid, "-", 8, 0);
        $uuid = substr_replace($uuid, "-", 13, 0);
        $uuid = substr_replace($uuid, "-", 18, 0);
        $uuid = substr_replace($uuid, "-", 23, 0);
    
        $_SESSION["uuid"] = $uuid;
        $_SESSION["username"] = $data["name"];
    
        if($this->db->isRegistered($uuid))
        {
            $_SESSION["logged"] = true;
            header("Location: /");
        }
        else
            header("Location: /join");
    }
}

