<?php

class Template
{
    private $name;
    private $values = array();
    
    public function __construct($name = "")
    {
        $this->set_name($name);
    }
    
    public function set_name($name)
    {
        $this->name = $name;
    }
    
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }
    
    public function html()
    {
        if(empty($this->name))
            throw new Exception("Template name not defined.");
        
        $filename = "template/$this->name.html";
        if(!file_exists($filename))
            throw new Exception("Template file not found <i>$filename</i>.");
        
        $html = file_get_contents($filename);
        
        foreach($this->values as $key => $value)
        {
            $html = str_replace("{".$key."}", $value, $html, $count);
            if($count == 0)
                throw new Exception("<i>$key</i> does not match any tag in template file <i>$filename</i>.");
        }
        
        if(preg_match_all("<{([^}]+)}>", $html, $matches) != 0)
        {
            $error = "Tag(s) not set in template file <i>$filename</i>:<ul>";
            foreach(array_unique($matches[1]) as $match)
                $error .= "<li>$match</li>";
            $error .= "</ul>";
    
            throw new Exception($error);
        }
        
        return $html;
    }
}
