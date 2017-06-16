<?php

class Template
{
    private $name;
    private $values = array();
    private $error;
    
    public function __construct($name = "")
    {
        $this->setName($name);
        $this->error = "";
    }
    
    public function setName($name)
    {
        $this->name = strtolower($name);
    }
    
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }
    
    public function setError($error)
    {
        $this->error = $error;
    }
    
    public function setOptional($key)
    {
        if(!isset($this->values[$key]))
            $this->values[$key] = "";
    }
    
    public function html()
    {
        if(empty($this->name))
            throw new Exception("Template name not defined.");
        
        $filename = "template/$this->name.html";
        if(!file_exists($filename))
            throw new Exception("Template file not found <i>$filename</i>.");
        
        $html = file_get_contents($filename);
        
        // replace optional {@error} tag
        $html = str_replace("{@error}", $this->error, $html);
        
        // replace {@...} tags by values
        foreach($this->values as $key => $value)
        {
            $html = str_replace("{@$key}", $value, $html, $count);
            if($count == 0)
                throw new Exception("<i>$key</i> does not match any tag in template file <i>$filename</i>.");
        }
        
        // ensure that there are no tags left
        if(preg_match_all("/{@([^\}]+)}/", $html, $matches) != 0)
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
