<?php

/**
 * Class representing a language translator.
 * Always translates from English to a specified target language.
 */
class Translator
{
    private $lang;
    private $translation;
    
    /**
     * Creates a translator instance.
     *
     * @param string $lang The target language
     */
    public function __construct($lang)
    {
        $this->lang = $lang;
        setlocale(LC_ALL, $lang);
        
        if($this->lang != "en")
        {
            $content = @file_get_contents("../data/lang/".$this->lang.".json");
            $this->translation = @json_decode($content, true);
        }
    }
    
    /**
     * Returns a translator with default browser language.
     *
     * @return Translator The new translator
     */
    public static function default()
    {
        $lang = locale_accept_from_http($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        return new self($lang);
    }
    
    /**
     * @return string The target language of the translator
     */
    public function lang()
    {
        return $this->lang;
    }
    
    /**
     * Translates text into the target language.
     *
     * @param string $text The text to be translated
     * @return string The new translated text
     */
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
    
    /**
     * Translates a HTML page.
     * The texts to be translated must be surrounded by [@...]
     *
     * @param string $html The HTML page
     * @return string The new translated HTML page
     */
    public function translateHTML($html)
    {
        preg_match_all("/\[@(.+)\]/", $html, $matches);
        foreach(array_unique($matches[1]) as $match)
            $html = str_replace("[@$match]", $this->translate($match), $html);
        
        return $html;
    }
    
    //////////////////////
    //      HELPERS     //
    //////////////////////
    
    /**
     * Makes the first character of a string uppercase.
     *
     * @param string $str The input string
     * @return string The resulting string
     */
    private static function upperFirst($str)
    {
        $first = mb_substr($str, 0, 1);
        $end = mb_substr($str, 1, mb_strlen($str) - 1);
        return mb_strtoupper($first).$end;
    }
    
    /**
     * Tells if the first character of a string is uppercase.
     *
     * @param string $str The input string
     * @return bool TRUE if the first character is uppercase, FALSE if not
     */
    private static function isUpperFirst($str)
    {
        $first = mb_substr($str, 0, 1);
        return mb_strtoupper($first) == $first;
    }
}

