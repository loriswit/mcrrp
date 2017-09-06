package me.olybri.mcrrp.listener;// Created by Loris Witschard on 8/22/2017.

import me.olybri.mcrrp.MCRRP;
import me.olybri.mcrrp.util.Lockable;
import org.bukkit.Material;
import org.bukkit.block.Block;
import org.bukkit.configuration.InvalidConfigurationException;
import org.bukkit.configuration.file.FileConfiguration;
import org.bukkit.configuration.file.YamlConfiguration;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.block.BlockBreakEvent;

import java.io.IOException;
import java.sql.SQLException;
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
    private static Map<Material, Set<Material>> materials = new HashMap<>();
    
    /**
     * Generates the block break rules and constructs the listener.
     *
     * @throws IOException                   if the <i>tools.yml</i> file cannot be read.
     * @throws InvalidConfigurationException if the <i>tools.yml</i> file is ill-formed.
     */
    public BlockBreakListener() throws IOException, InvalidConfigurationException
    {
        FileConfiguration config = new YamlConfiguration();
        config.load("../data/item/tools.yml");
        
        for(String toolNameList : config.getKeys(false))
        {
            for(String blockName : config.getStringList(toolNameList))
            {
                Material material = Material.getMaterial(blockName);
                if(material == null)
                    continue;
                
                materials.putIfAbsent(material, new HashSet<>());
                
                for(String toolName : toolNameList.split("\\s"))
                {
                    Material tool = Material.getMaterial(toolName.trim());
                    if(tool != null)
                        materials.get(material).add(tool);
                }
            }
        }
    }
    
    @EventHandler
    public void onBlockBreakEvent(BlockBreakEvent event)
    {
        Player player = event.getPlayer();
        
        Material tool = player.getInventory().getItemInMainHand().getType();
        Block block = event.getBlock();
        Material material = block.getType();
        
        Set<Material> tools = materials.get(material);
        if(tools != null && !tools.contains(tool))
            event.setCancelled(true);
        
        else try
        {
            Lockable lockable = Lockable.create(block, player);
            if(lockable != null && lockable.locked())
                lockable.unlock();
        }
        catch(SQLException e)
        {
            MCRRP.error(e, player);
        }
    }
}
