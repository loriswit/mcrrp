package me.olybri.mcrrp.listener;// Created by Loris Witschard on 9/2/2017.

import me.olybri.mcrrp.util.Message;
import me.olybri.mcrrp.util.Wallet;
import org.bukkit.entity.Entity;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.entity.EntityPickupItemEvent;

import static me.olybri.mcrrp.util.Translation.tr;

/**
 * Class listening to pickup item events so that picked up wallets are added to the citizen's balance.
 */
public class PickupItemListener implements Listener
{
    @EventHandler
    void onEntityPickupItemEvent(EntityPickupItemEvent event)
    {
        if(!(event.getEntity() instanceof Player))
            return;
        
        Player player = (Player) event.getEntity();
        Entity entity = event.getItem();
        
        Wallet wallet = Wallet.create(entity);
        if(wallet != null)
        {
            entity.remove();
            wallet.pickup(player);
            new Message(tr("Picked up") + " {value:$" + wallet.amount() + "}.").send(player);
            
            event.setCancelled(true);
        }
    }
}
