<?php

class Translator
{
    private $lang;
    
    public function __construct($lang)
    {
        $this->lang = $lang;
        setlocale(LC_ALL, $lang);
    }
    
    // returns a Translator with default browser language
    public static function default()
    {
        $lang = locale_accept_from_http($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        return new self($lang);
    }
    
    public function lang()
    {
        return $this->lang;
    }
    
    public function translate($text)
    {
        if($this->lang == "en")
            return $text;
    
        $translation = @file_get_contents("language/".$this->lang.".json");
        $res = @json_decode($translation, true)[$text];
    
        if(!isset($res))
            return $text;
    
        return $res;
    }
    
    public function translateHTML($html)
    {
        // replace [@...] tags by translations
        preg_match_all("/\[@(.+)\]/", $html, $matches);
        foreach(array_unique($matches[1]) as $match)
            $html = str_replace("[@$match]", $this->translate($match), $html);
        
        return $html;
    }
}

