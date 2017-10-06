<?php

/**
 * Class representing a date.
 */
class Date
{
    private $timestamp;
    
    /**
     * Constructs a date object.
     *
     * @param int $timestamp The UNIX timestamp
     */
    function __construct($timestamp)
    {
        $this->timestamp = $timestamp;
    }
    
    /**
     * Converts the date to a string relative to the current time.
     *
     * @return string The date relative to the current time
     */
    function __toString()
    {
        $diff = time() - $this->timestamp;
        
        if($diff < 1) return "now";
        if($diff < 60) return "just now";
        if($diff < 120) return "1 minute ago";
        if($diff < 3600) return floor($diff / 60)." minutes ago";
        if($diff < 7200) return "1 hour ago";
        if($diff < 86400) return floor($diff / 3600)." hours ago";
        
        $diff = floor($diff / 86400);
        if($diff == 1) return "yesterday";
        if($diff < 7) return $diff." days ago";
        
        return date("j F Y", $this->timestamp);
    }
}
