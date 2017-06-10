<?php

$title = "Log in";
$error = "";

if(isset($_POST["login"]))
{
    $name = $_POST["username"];
    
    $json = @file_get_contents("https://api.mojang.com/users/profiles/minecraft/$name");
    
    if($json === false || empty($json))
        $error = tr("Invalid username. Please try again.");
    
    else
    {
        $data = json_decode($json, true);
        $uuid = $data["id"];
        $uuid = substr_replace($uuid, "-", 8, 0);
        $uuid = substr_replace($uuid, "-", 13, 0);
        $uuid = substr_replace($uuid, "-", 18, 0);
        $uuid = substr_replace($uuid, "-", 23, 0);
    
        $_SESSION["uuid"] = $uuid;
        $_SESSION["username"] = $data["name"];
        header("Location: /");
    }
}

$body_tpl = new Template("login");
$body_tpl->set("error", $error);
