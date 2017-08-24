package me.olybri.mcrrp.listener;// Created by Loris Witschard on 8/22/2017.

import org.bukkit.Material;
import org.bukkit.configuration.file.FileConfiguration;
import org.bukkit.configuration.file.YamlConfiguration;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.block.BlockBreakEvent;

import java.util.HashMap;
import java.util.HashSet;
import java.util.Map;
import java.util.Set;

/**
 * Class listening to block break events.
 * Blocks that can only be broken by specific tools are listed in the <i>tools.yml</i> config file.
 */
public class BlockBreakListener implements Listener
{
    private static Map<Material, Set<Material>> blocks = new HashMap<>();
    
    /**
     * Generates the block break rules and constructs the listener.
     *
     * @throws Exception if the <i>tools.yml</i> file can't be read.
     */
    public BlockBreakListener() throws Exception
    {
        FileConfiguration config = new YamlConfiguration();
        config.load("../tools.yml");
        
        for(String toolNameList : config.getKeys(false))
        {
            for(String blockName : config.getStringList(toolNameList))
            {
                Material block = Material.matchMaterial(blockName);
                if(block == null)
                    continue;
                
                blocks.putIfAbsent(block, new HashSet<>());
                
                for(String toolName : toolNameList.split("/"))
                {
                    Material tool = Material.matchMaterial(toolName.trim());
                    if(tool != null)
                        blocks.get(block).add(tool);
                }
            }
        }
    }
    
    @EventHandler
    public void onBlockBreakEvent(BlockBreakEvent event)
    {
        Player player = event.getPlayer();
        
        Material tool = player.getInventory().getItemInMainHand().getType();
        Material block = event.getBlock().getType();
        
        Set<Material> tools = blocks.get(block);
        if(tools != null && !tools.contains(tool))
            event.setCancelled(true);
    }
}
