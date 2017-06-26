<?php

session_start();

require_once "vendor/autoload.php";

spl_autoload_register(function($class)
{
    $filename = "include/".strtolower($class).".php";
    if(file_exists($filename))
        include $filename;
    
    $filename = "page/".strtolower($class).".php";
    if(file_exists($filename))
        include $filename;
});

if(isset($_POST["logout"]))
    unset($_SESSION["logged"]);

if(isset($_POST["lang"]))
    $_SESSION["lang"] = $_POST["lang"];

if(isset($_SESSION["lang"]))
    $translator = new Translator($_SESSION["lang"]);
else
    $translator = Translator::default();

function tr($text)
{
    global $translator;
    return $translator->translate($text);
}

define("LANG", $translator->lang());
define("LOGGED", isset($_SESSION["logged"]));

if(empty($_GET["class"]))
    $class = LOGGED ? "Home" : "Login";
else
    $class = ucfirst($_GET["class"]);

try
{
    if(class_exists($class))
        $page = new $class();
    else
        $page = new NotFound();
    
    $html = $page->render();
}
catch(Exception $e)
{
    die("<br><b>Error</b>: ".$e->getMessage()."<br>"
        ."<br><b>Thrown</b> in ".$e->getFile()." on line ".$e->getLine()."<br>"
        ."<br><b>Stack trace</b>:<br>".nl2br($e->getTraceAsString()));
}

$html = $translator->translateHTML($html);
echo $html;
