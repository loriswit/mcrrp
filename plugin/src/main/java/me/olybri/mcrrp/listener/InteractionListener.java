package me.olybri.mcrrp.listener;// Created by Loris Witschard on 7/4/2017.

import me.olybri.mcrrp.interaction.Interaction;
import me.olybri.mcrrp.util.Lockable;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.block.Action;
import org.bukkit.event.player.PlayerEvent;
import org.bukkit.event.player.PlayerInteractEntityEvent;
import org.bukkit.event.player.PlayerInteractEvent;

import java.util.HashMap;
import java.util.Map;
import java.util.UUID;

/**
 * Class listening to players interactions with the world and others entities.
 */
public class InteractionListener implements Listener
{
    private static Map<UUID, Interaction> interactions = new HashMap<>();
    
    /**
     * Static function that defines which interaction to listen to for a specific player.
     * Only one interaction per player can be listened to.
     * If the interaction is <i>null</i>, no interactions will be listened to for this player.
     *
     * @param player      The player involved in the interaction
     * @param interaction The interaction to listen to
     */
    public static void putInteraction(Player player, Interaction interaction)
    {
        interactions.put(player.getUniqueId(), interaction);
    }
    
    @EventHandler
    public void onPlayerInteractEntity(PlayerInteractEntityEvent event)
    {
        applyInteraction(event);
    }
    
    @EventHandler
    public void onPlayerInteract(PlayerInteractEvent event)
    {
        Action action = event.getAction();
        if(action == Action.RIGHT_CLICK_BLOCK)
        {
            applyInteraction(event);
            
            if(!event.isCancelled())
            {
                Lockable lockable = Lockable.create(event.getClickedBlock(), event.getPlayer());
                if(lockable != null && lockable.locked())
                    event.setCancelled(true);
            }
        }
    }
    
    /**
     * Executes the interaction being listened to, if any.
     * If the interaction process succeeded, the interaction is not listened to anymore.
     *
     * @param event The associated event
     */
    private void applyInteraction(PlayerEvent event)
    {
        UUID uuid = event.getPlayer().getUniqueId();
        
        if(!interactions.containsKey(uuid) || interactions.get(uuid) == null)
            return;
        
        if(interactions.get(uuid).apply(event))
            interactions.remove(uuid);
    }
}
