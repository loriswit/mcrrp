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
        $username = $_POST["username"];
        
        if(CONFIG["settings"]["online"])
        {
            $MCAuth = new MCAuth\Api();
            
            try
            {
                $account = $MCAuth->sendAuth($username, $_POST["password"]);
            }
            catch(Exception $e)
            {
                throw new InvalidInputException("Invalid username or password.");
            }
            
            $uuid = $account->uuid;
            $username = $account->username;
        }
        else
        {
            if(!preg_match("/^\w{3,16}$/", $username))
                throw new InvalidInputException("Invalid username.");
            
            // generate an offline UUID
            $uuid = md5("OfflinePlayer:$username");
            $uuid[12] = '3';
            $uuid[16] = dechex(intval($uuid[16], 16) % 4 + 8);
        }
        
        $uuid = substr_replace($uuid, "-", 8, 0);
        $uuid = substr_replace($uuid, "-", 13, 0);
        $uuid = substr_replace($uuid, "-", 18, 0);
        $uuid = substr_replace($uuid, "-", 23, 0);
        
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

