package me.olybri.mcrrp.interaction;// Created by Loris Witschard on 7/4/2017.

import me.olybri.mcrrp.MCRRP;
import org.bukkit.entity.Entity;
import org.bukkit.entity.Player;
import org.bukkit.event.player.PlayerEvent;
import org.bukkit.event.player.PlayerInteractEntityEvent;

public abstract class PlayerInteraction implements Interaction
{
    @Override
    public final boolean apply(PlayerEvent event)
    {
        if(!(event instanceof PlayerInteractEntityEvent))
            return false;
        
        Entity entity = ((PlayerInteractEntityEvent) event).getRightClicked();
        if(!(entity instanceof Player))
            return false;
        
        Player player = (Player) entity;
        
        try
        {
            boolean success = run(event.getPlayer(), player);
            ((PlayerInteractEntityEvent) event).setCancelled(success);
            return success;
        }
        catch(Exception e)
        {
            MCRRP.error(e, player);
        }
        
        return false;
    }
    
    protected abstract boolean run(Player player, Player target) throws Exception;
}
