<?php

/**
 * Class representing a HTML page template
 */
class Template
{
    private $name;
    private $values = array();
    private $error;
    
    /**
     * Creates a HTML template instance.
     *
     * @param string $name The name of the HTML template (should match a file in
     * the 'template' folder)
     */
    public function __construct($name)
    {
        $this->name = strtolower($name);
        $this->error = "";
    }
    
    /**
     * Sets the value for a specific key.
     *
     * @param string $key A valid key of the HTML template
     * @param mixed $value A value for the key
     */
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }
    
    /**
     * Sets the error (optional value) that occurred on the page.
     *
     * @param string $error The error message
     */
    public function setError($error)
    {
        $this->error = $error;
    }
    
    /**
     * Marks a key as optional.
     *
     * @param string $key A valid key of the HTML template
     */
    public function setOptional($key)
    {
        if(!isset($this->values[$key]))
            $this->values[$key] = "";
    }
    
    /**
     * Returns the HTML template code filled with values.
     *
     * @return string The HTML code
     * @throws Exception if the template file was not found, if a key doesn't
     * match any tag or if some tags are not set
     */
    public function html()
    {
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
