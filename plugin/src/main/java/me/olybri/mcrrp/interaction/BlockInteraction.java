package me.olybri.mcrrp.interaction;// Created by Loris Witschard on 7/4/2017.

import org.bukkit.block.Block;
import org.bukkit.entity.Player;
import org.bukkit.event.player.PlayerEvent;
import org.bukkit.event.player.PlayerInteractEvent;

import java.sql.SQLException;

public abstract class BlockInteraction implements Interaction
{
    @Override
    public final boolean apply(PlayerEvent event) throws SQLException
    {
        if(!(event instanceof PlayerInteractEvent))
            return false;
    
        Block block = ((PlayerInteractEvent) event).getClickedBlock();
    
        run(event.getPlayer(), block);
        ((PlayerInteractEvent) event).setCancelled(true);
        return true;
    }
    
    protected abstract void run(Player player, Block target) throws SQLException;
}
