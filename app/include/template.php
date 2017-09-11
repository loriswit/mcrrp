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
        if(is_array($value))
            $this->values[$key] = array_values($value);
        else
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
            if(is_array($value))
            {
                if(empty($value))
                    $html = str_replace("{@$key}", "", $html, $count);
                else
                {
                    $regex = "/^.*{@$key}.*$/m";
                    $count = preg_match_all($regex, $html, $matches);
                    $diff = count($value) - count($matches[0]);
                    if($diff > 0)
                    {
                        $line = end($matches[0]);
                        $html = str_replace("$line\n", str_repeat("$line\n", $diff + 1), $html);
                        preg_match_all($regex, $html, $matches);
                    }
                    
                    for($i = 0; $i < count($matches[0]); ++$i)
                    {
                        $line = str_replace("{@$key}", $value[$i % count($value)], $matches[0][$i]);
                        $pos = strpos($html, $matches[0][$i]);
                        $html = substr_replace($html, $line, $pos, strlen($matches[0][$i]));
                    }
                }
            }
            else
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
