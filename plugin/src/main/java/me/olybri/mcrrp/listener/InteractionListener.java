package me.olybri.mcrrp.listener;// Created by Loris Witschard on 7/4/2017.

import me.olybri.mcrrp.interaction.Interaction;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.player.PlayerEvent;
import org.bukkit.event.player.PlayerInteractEntityEvent;
import org.bukkit.event.player.PlayerInteractEvent;

import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;
import java.util.UUID;

public class InteractionListener implements Listener
{
    private static Map<UUID, Interaction> interactions = new HashMap<>();
    
    public static void putInteraction(Player player, Interaction interaction)
    {
        interactions.put(player.getUniqueId(), interaction);
    }
    
    @EventHandler
    public void onPlayerInteractEntity(PlayerInteractEntityEvent event) throws SQLException
    {
        applyInteraction(event);
    }
    
    @EventHandler
    public void onPlayerInteract(PlayerInteractEvent event) throws SQLException
    {
        applyInteraction(event);
    }
    
    private void applyInteraction(PlayerEvent event) throws SQLException
    {
        UUID uuid = event.getPlayer().getUniqueId();
        
        if(!interactions.containsKey(uuid) || interactions.get(uuid) == null)
            return;
        
        if(interactions.get(uuid).apply(event))
            interactions.remove(uuid);
    }
}
