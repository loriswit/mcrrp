<?php

session_start();

if(isset($_POST["logout"]))
{
    unset($_SESSION["uuid"]);
    header("Location:/");
}
if(isset($_POST["lang"]))
{
    $_SESSION["lang"] = $_POST["lang"];
    header("Refresh:0");
}

try
{
    require_once "vendor/autoload.php";
    
    require_once "include/translator.php";
    require_once "include/template.php";
    require_once "include/database.php";
    
    $db = new Database();
    
    // if not logged in, redirect to login page
    if(!isset($_SESSION["uuid"]))
    {
        if(!empty($_GET["r"]))
            header("Location:/");
        
        $request = "login";
    }
    else
        $request = empty($_GET["r"]) ? "home" : $_GET["r"];
    
    require "page/$request.php";
    
    if($request == "login")
        $body_tpl = new Template("login");
    
    else
    {
        if(!isset($title) || !isset($tpl))
            throw new Exception("Title and/or template not defined.");
        
        $uuid = $_SESSION["uuid"];
        $citizen = $db->citizen($uuid);
    
        $body_tpl = new Template("body");
        $body_tpl->set("uuid", $uuid);
        $body_tpl->set("name", $citizen["first_name"]." ".$citizen["last_name"]);
        $body_tpl->set("code", $citizen["code"]);
        $body_tpl->set("role", "n/a");
        $body_tpl->set("balance", $citizen["balance"]);
        $body_tpl->set("title", $title);
        $body_tpl->set("content", $tpl->html());
    }
    
    $main_tpl = new Template("main");
    $main_tpl->set("lang", $lang);
    $main_tpl->set("title", $title);
    $main_tpl->set("body", $body_tpl->html());
    $main_tpl->set("en", $lang == "en" ? "selected" : "");
    $main_tpl->set("fr", $lang == "fr" ? "selected" : "");
    echo $main_tpl->html();
}
catch(Exception $e)
{
    die("<br><b>Error</b>: ".$e->getMessage()."<br>"
        ."<br><b>Thrown</b> in ".$e->getFile()." on line ".$e->getLine()."<br>"
        ."<br><b>Stack trace</b>:<br>".nl2br($e->getTraceAsString()));
}

