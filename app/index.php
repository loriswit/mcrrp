<?php

use Symfony\Component\Yaml\Yaml;

session_start();

require_once "vendor/autoload.php";

spl_autoload_register(function($class)
{
    $filename = "util/".strtolower($class).".php";
    if(file_exists($filename))
        require_once $filename;
    
    $filename = "page/".strtolower($class).".php";
    if(file_exists($filename))
        require_once $filename;
});

mb_internal_encoding("UTF-8");

define("CONFIG", Yaml::parse(file_get_contents("../config.yml")));

if(isset($_POST["logout"]))
    session_unset();

if(isset($_POST["lang"]))
    $_SESSION["lang"] = $_POST["lang"];

if(isset($_SESSION["lang"]))
    $translator = new Translator($_SESSION["lang"]);
else
    $translator = new Translator(CONFIG["settings"]["lang"]);

function tr($text)
{
    global $translator;
    return $translator->translate($text);
}

define("LANG", $translator->lang());
define("LOGGED", isset($_SESSION["logged"]));

$args = explode("/", $_SERVER["REQUEST_URI"]);
$class = ucfirst($args[1]);
$args = array_filter(array_slice($args, 2));

if(empty($class))
    $class = LOGGED ? "Home" : "Login";

try
{
    if(class_exists($class))
        $page = new $class($args);
    else
        $page = new NotFound($args);
    
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
