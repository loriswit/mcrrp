<?php

use Symfony\Component\Yaml\Yaml;

/**
 * Utility class providing functions to convert materials into item names and vice versa.
 */
class Items
{
    /** @var array Array storing all item names */
    private static $itemNames;
    
    /**
     * Loads all item names from a YAML file.
     */
    private static function loadItems()
    {
        if(empty(self::$itemNames))
            self::$itemNames = Yaml::parse(file_get_contents("../data/item/names.yml"));
    }
    
    /**
     * Returns the item name associated with the material.
     *
     * @param string $material The material name ("material.damage")
     * @return bool|string The item name, or FALSE if no items match the material
     */
    public static function getName($material)
    {
        self::loadItems();
        
        $args = explode(".", $material);
        $key = strtoupper($args[0]);
        $damage = (count($args) == 2 ? $args[1] : 0);
        
        if(!isset(self::$itemNames[$key][$damage]))
            return false;
        
        return self::$itemNames[$key][$damage];
    }
    
    /**
     * Returns the material associated with the item name.
     *
     * @param string $name The item name
     * @return bool|string The material name ("material.damage"), or FALSE if no materials match the item
     */
    public static function getMaterial($name)
    {
        self::loadItems();
        
        foreach(self::$itemNames as $material => $items)
            if(is_array($items))
                foreach($items as $damage => $item)
                    if(strcasecmp($item, trim($name)) == 0)
                        return "$material.$damage";
        
        return false;
    }
}
