package me.olybri.mcrrp.listener;// Created by Loris Witschard on 9/2/2017.

import me.olybri.mcrrp.MCRRP;
import me.olybri.mcrrp.util.Database;
import me.olybri.mcrrp.util.Message;
import org.bukkit.Material;
import org.bukkit.Sound;
import org.bukkit.entity.Item;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.entity.EntityPickupItemEvent;
import org.bukkit.inventory.ItemStack;
import org.bukkit.inventory.meta.BookMeta;

import static me.olybri.mcrrp.util.Translation.tr;

public class PickupItemListener implements Listener
{
    @EventHandler
    void onEntityPickupItemEvent(EntityPickupItemEvent event)
    {
        if(!(event.getEntity() instanceof Player))
            return;
        
        Player player = (Player) event.getEntity();
        Item itemEntity = event.getItem();
        ItemStack item = itemEntity.getItemStack();
        
        if(item.getType() == Material.WRITTEN_BOOK)
        {
            BookMeta book = ((BookMeta) item.getItemMeta());
            if(book.getAuthor().equals("wallet"))
            {
                try
                {
                    String amount = book.getTitle().replaceAll("[^\\d]", "");
                    Database.addMoney(player, Integer.parseUnsignedInt(amount));
                }
                catch(Exception e)
                {
                    MCRRP.error(e, player);
                }
                
                itemEntity.remove();
                player.playSound(player.getLocation(), Sound.ENTITY_ITEM_PICKUP, 1, 1);
                new Message(tr("Picked up") + " {value:" + book.getTitle() + "}.").send(player);
                
                event.setCancelled(true);
            }
        }
    }
}
