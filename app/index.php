<?php

try
{
    require_once "vendor/autoload.php";
    
    require_once "include/template.php";
    require_once "include/database.php";
    
    $db = new Database();
    
    session_start();
    if(!isset($_SESSION["uuid"]))
        $request = "login";
    else
        $request = empty($_GET["r"]) ? "home" : $_GET["r"];
    
    require "page/$request.php";
    
    if($request == "login")
        $main_tpl = new Template("login");
    
    else
    {
        if(!isset($title) || !isset($tpl))
            throw new Exception("Title and/or template not defined.");
        
        $main_tpl = new Template("main");
        $main_tpl->set("title", $title);
    
        $uuid = $_SESSION["uuid"];
        $citizen = $db->citizen($uuid);
        $main_tpl->set("uuid", $uuid);
        $main_tpl->set("name", $citizen["first_name"]." ".$citizen["last_name"]);
        $main_tpl->set("code", $citizen["code"]);
        $main_tpl->set("roles", "n/a");
        $main_tpl->set("balance", $citizen["balance"]);
        
        $main_tpl->set("content", $tpl->html());
    }
    
    echo $main_tpl->html();
}
catch(Exception $e)
{
    die("<br><b>Error</b>: ".$e->getMessage()."<br>"
        ."<br><b>Thrown</b> in ".$e->getFile()." on line ".$e->getLine()."<br>"
        ."<br><b>Stack trace</b>:<br>".nl2br($e->getTraceAsString()));
}

