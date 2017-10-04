<?php

/**
 * Page displaying a login form
 */
class Login extends Page
{
    protected $visitorOnly = true;
    
    protected function title()
    {
        return "Log in";
    }
    
    protected function run()
    {
        $this->set("online", CONFIG["settings"]["online"]);
    }
    
    protected function submit()
    {
        $name = $_POST["username"];
        
        if(CONFIG["settings"]["online"])
        {
            $MCAuth = new MCAuth\Api();
            
            try
            {
                $account = $MCAuth->sendAuth($name, $_POST["password"]);
            }
            catch(Exception $e)
            {
                throw new InvalidInputException("Invalid username or password.");
            }
            
            $uuid = $account->uuid;
            $uuid = substr_replace($uuid, "-", 8, 0);
            $uuid = substr_replace($uuid, "-", 13, 0);
            $uuid = substr_replace($uuid, "-", 18, 0);
            $uuid = substr_replace($uuid, "-", 23, 0);
            $username = $account->username;
        }
        else
        {
            $json = @file_get_contents("https://www.fabianwennink.nl/projects/OfflineUUID/api/$name");
            $data = json_decode($json, true);
            
            if($data == false || isset($data["error"]))
                throw new InvalidInputException("Invalid username.");
            
            $uuid = $data["uuid"];
            $username = $data["username"];
        }
        
        $_SESSION["uuid"] = $uuid;
        $_SESSION["username"] = $username;
        
        if($this->db->isRegistered($uuid))
        {
            $_SESSION["logged"] = true;
            header("Location: /");
        }
        else
            header("Location: /join");
    }
}

