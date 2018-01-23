package me.olybri.mcrrp.listener;// Created by Loris Witschard on 9/9/2017.

import me.olybri.mcrrp.MCRRP;
import me.olybri.mcrrp.util.Database;
import org.bukkit.Material;
import org.bukkit.entity.HumanEntity;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.inventory.PrepareItemCraftEvent;
import org.bukkit.inventory.ItemStack;

import java.sql.SQLException;

/**
 * Class listening to craft events so that only specific items can be crafted by the citizen.
 */
public class ItemCraftListener implements Listener
{
    @EventHandler
    public void onPrepareItemCraftEvent(PrepareItemCraftEvent event)
    {
        if(event.getRecipe() != null)
        {
            HumanEntity entity = event.getView().getPlayer();
            if(entity instanceof Player)
            {
                Player player = (Player) entity;
                ItemStack result = event.getRecipe().getResult();
                boolean allowed = false;
                
                try
                {
                    for(ItemStack item : Database.materials(player))
                        if(result.isSimilar(item))
                        {
                            allowed = true;
                            break;
                        }
                }
                catch(SQLException e)
                {
                    MCRRP.error(e, player);
                }
                
                if(!allowed)
                    event.getInventory().setResult(new ItemStack(Material.AIR));
            }
        }
    }
}
