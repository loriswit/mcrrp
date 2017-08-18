package me.olybri.mcrrp.interaction;// Created by Loris Witschard on 7/4/2017.

import me.olybri.mcrrp.MCRRP;
import org.bukkit.entity.Entity;
import org.bukkit.entity.Player;
import org.bukkit.event.player.PlayerEvent;
import org.bukkit.event.player.PlayerInteractEntityEvent;

/**
 * Abstract class representing an interaction with another player.
 */
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
    
    /**
     * Runs the player interaction process.
     *
     * @param player The source player
     * @param target The target player
     * @return <i>true</i> if the process succeeded, <i>false</i> if not
     */
    protected abstract boolean run(Player player, Player target) throws Exception;
}
