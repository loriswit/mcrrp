package me.olybri.mcrrp.util;// Created by Loris Witschard on 8/25/2017.

import org.bukkit.configuration.InvalidConfigurationException;
import org.bukkit.configuration.file.FileConfiguration;
import org.bukkit.configuration.file.YamlConfiguration;
import org.bukkit.inventory.ItemStack;

import java.io.IOException;

/**
 * Class representing an item name.
 */
public class ItemName
{
    private static FileConfiguration items = new YamlConfiguration();
    
    private String name;
    
    /**
     * Loads all item names in memory.
     *
     * @throws IOException                   if the <i>names.yml</i> file cannot be read.
     * @throws InvalidConfigurationException if the <i>names.yml</i> file is ill-formed.
     */
    public static void init() throws IOException, InvalidConfigurationException
    {
        items.load("../data/item/names.yml");
    }
    
    /**
     * Constructs an item name according to any item stack.
     *
     * @param item The item stack
     */
    public ItemName(ItemStack item)
    {
        name = items.getString(item.getType() + "." + item.getDurability());
        if(name == null)
            name = items.getString(item.getType() + ".0") + " (damaged)";
    }
    
    @Override
    public String toString()
    {
        return name;
    }
}
