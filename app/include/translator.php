<?php

class Translator
{
    private $lang;
    private $translation;
    
    public function __construct($lang)
    {
        $this->lang = $lang;
        setlocale(LC_ALL, $lang);
        
        if($this->lang != "en")
        {
            $content = @file_get_contents("../lang/".$this->lang.".json");
            $this->translation = @json_decode($content, true);
        }
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
        $key = mb_strtolower($text);
        
        if(!isset($this->translation[$key]))
            return $text;
        
        if($this->isUpperFirst($text))
            return $this->upperFirst($this->translation[$key]);
        else
            return $this->translation[$key];
    }
    
    public function translateHTML($html)
    {
        // replace [@...] tags by translations
        preg_match_all("/\[@(.+)\]/", $html, $matches);
        foreach(array_unique($matches[1]) as $match)
            $html = str_replace("[@$match]", $this->translate($match), $html);
        
        return $html;
    }
    
    private function upperFirst($str)
    {
        $first = mb_substr($str, 0, 1);
        $end = mb_substr($str, 1, mb_strlen($str) - 1);
        return mb_strtoupper($first).$end;
    }
    
    private function isUpperFirst($str)
    {
        $first = mb_substr($str, 0, 1);
        return mb_strtoupper($first) == $first;
    }
}

