package me.olybri.mcrrp.util;// Created by Loris Witschard on 8/25/2017.

import org.bukkit.configuration.InvalidConfigurationException;
import org.bukkit.configuration.file.FileConfiguration;
import org.bukkit.configuration.file.YamlConfiguration;
import org.bukkit.inventory.ItemStack;

import java.io.IOException;

import static me.olybri.mcrrp.util.Translation.tr;

public class ItemName
{
    private static FileConfiguration items = new YamlConfiguration();
    
    private String name;
    
    public static void init() throws IOException, InvalidConfigurationException
    {
        items.load("../data/item/names.yml");
    }
    
    public ItemName(ItemStack item)
    {
        name = items.getString(item.getType() + "." + item.getDurability());
    }
    
    @Override
    public String toString()
    {
        if(name == null)
            return tr("Invalid item");
        
        return name;
    }

//        private static class ItemData
//    {
//        int type = 0;
//        short data_value = 0;
//        String name = "N/A";
//        String text_type = "";
//    }
//
//    private static List<ItemData> itemDataList;
//
//    public static void init() throws IOException
//    {
//        Path filePath = Paths.get("../data/items.json");
//        String content = new String(Files.readAllBytes(filePath), StandardCharsets.UTF_8);
//
//        Type type = new TypeToken<List<ItemData>>()
//        {
//        }.getType();
//        itemDataList = new Gson().fromJson(content, type);
//    }
//
//    private ItemData itemData = new ItemData();
//
//    //temp
//    private boolean b = false;
//
//    public ItemName(ItemStack item)
//    {
//        if(itemDataList == null)
//            return;
//
//        for(ItemData itemData : itemDataList)
//        {
//            if(itemData.text_type.toUpperCase().equals(item.getType().name()))
//                this.itemData = itemData;
//
//            // temp
//            else if(itemData.type == item.getTypeId())
//            {
//                this.itemData = itemData;
//                b = true;
//            }
//        }
//    }
//
//    @Override
//    public String toString()
//    {
//        // temp
//        if(!b)
//            return "#" + itemData.text_type;
//
//        return itemData.text_type;
//    }
}
