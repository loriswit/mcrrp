<?php

if(isset($_SESSION["lang"]))
    $lang = $_SESSION["lang"];
else
    $lang = locale_accept_from_http($_SERVER["HTTP_ACCEPT_LANGUAGE"]);

function tr($text)
{
    global $lang;
    if($lang == "en")
        return $text;
    
    $translation = @file_get_contents("language/$lang.json");
    $res = @json_decode($translation, true)[$text];
    
    if(!isset($res))
        return $text;
    
    return $res;
}
