package me.olybri.mcrrp.interaction;// Created by Loris Witschard on 7/4/2017.

import org.bukkit.entity.Entity;
import org.bukkit.entity.Player;
import org.bukkit.event.player.PlayerEvent;
import org.bukkit.event.player.PlayerInteractEntityEvent;

import java.sql.SQLException;

public abstract class PlayerInteraction implements Interaction
{
    @Override
    public final boolean apply(PlayerEvent event) throws SQLException
    {
        if(!(event instanceof PlayerInteractEntityEvent))
            return false;
        
        Entity entity = ((PlayerInteractEntityEvent) event).getRightClicked();
        if(!(entity instanceof Player))
            return false;
        
        run(event.getPlayer(), (Player) entity);
        ((PlayerInteractEntityEvent) event).setCancelled(true);
        return true;
    }
    
    protected abstract void run(Player player, Player target) throws SQLException;
}
