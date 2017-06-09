<?php

if(isset($_POST["submit"]))
{
    $name = $_POST["username"];
    
    $json = @file_get_contents("https://api.mojang.com/users/profiles/minecraft/$name");
    
    if($json === false || empty($json))
        echo "<p>Nom d'utilisateur invalide.</p>";
    
    else
    {
        $uuid = json_decode($json, true)["id"];
        $uuid = substr_replace($uuid, "-", 8, 0);
        $uuid = substr_replace($uuid, "-", 13, 0);
        $uuid = substr_replace($uuid, "-", 18, 0);
        $uuid = substr_replace($uuid, "-", 23, 0);
        
        $citizen = $db->citizen($uuid);
        if(empty($citizen))
            echo "<p>Pas inscrit</p>";
        
        else
        {
            session_start(["use_only_cookies" => true, "cookie_lifetime" => 86400]);
            $_SESSION["uuid"] = $uuid;
            header("Location: /");
        }
    }
}
