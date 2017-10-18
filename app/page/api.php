<?php

/**
 * Web API providing informations about valid codes.
 */
class Api extends Page
{
    protected $argsCount = 2;
    
    protected function title()
    {
        return "api";
    }
    
    protected function run()
    {
        if(count($this->args) == 2)
        {
            $value = strtoupper($this->args[1]);
            switch($this->args[0])
            {
                case "icon":
                    if(strpos($value, "-") === false)
                        $value .= "-0";
                    
                    header("Location: https://olybri.github.io/bing/icons/$value.png");
                    exit;
                
                case "code":
                    if(!LOGGED || $value == $this->citizen["code"])
                        echo 0;
                    else
                        echo $this->db->citizenExists($this->args[1]) ? 1 : 0;
                    exit;
            }
        }
        else
            Header("Location: /");
    }
    
    protected function submit()
    {
    }
}
