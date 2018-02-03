<?php

/**
 * Web API providing various resources.
 */
class Api extends Page
{
    protected $argsCount = 2;
    
    protected function run()
    {
        if(count($this->args) != 2)
        {
            header("Location: /");
            exit;
        }
        
        $value = strtoupper($this->args[1]);
        switch($this->args[0])
        {
            case "icon":
                if(strpos($value, "-") === false)
                    $value .= "-0";
                
                header("Location: https://olybri.github.io/bing/icons/$value.png");
                exit;
            
            case "avatar":
                header("Location: https://crafatar.com/avatars/$value?size=32&overlay");
                exit;
            
            case "code":
                if(!LOGGED)
                    echo 0;
                else
                    echo $this->db->citizenExists($this->args[1]) ? 1 : 0;
                exit;
            
            default:
                header("Location: /");
                exit;
        }
    }
    
    protected function submit()
    {
    }
    
    protected function title()
    {
    }
}
