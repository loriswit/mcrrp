<?php

class Code
{
    private const CODE_CHARS = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private const CODE_LENGTH = 4;
    
    // generates a code according to the name
    public static function generate($name)
    {
        $code = "";
        preg_match_all("/\\p{Lu}/u", $name, $matches);
        foreach($matches[0] as $letter)
            if(strpos(Code::CODE_CHARS, $letter) !== false)
                $code .= $letter;
        
        if(strlen($code) == 0)
            return Code::random();
        
        if(strlen($code) > Code::CODE_LENGTH - 1)
            $code = substr($code, 0, Code::CODE_LENGTH - 1);
        
        while(strlen($code) != Code::CODE_LENGTH)
            $code .= mt_rand(0, 9);
        
        return $code;
    }
    
    // generates a random code
    public static function random()
    {
        $code = "";
        while(strlen($code) != Code::CODE_LENGTH)
            $code .= Code::CODE_CHARS[mt_rand(0, strlen(Code::CODE_CHARS) - 1)];
        
        return $code;
    }
}
