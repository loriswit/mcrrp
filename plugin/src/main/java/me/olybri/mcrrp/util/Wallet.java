package me.olybri.mcrrp.util;// Created by Loris Witschard on 9/2/2017.

import me.olybri.mcrrp.MCRRP;
import org.bukkit.Material;
import org.bukkit.Sound;
import org.bukkit.entity.Entity;
import org.bukkit.entity.Item;
import org.bukkit.entity.Player;
import org.bukkit.inventory.ItemStack;
import org.bukkit.inventory.meta.BookMeta;

import java.sql.SQLException;

import static me.olybri.mcrrp.util.Translation.tr;

/**
 * Class representing a wallet that can be dropped or picked up.
 */
public class Wallet
{
    private int amount;
    private final static String identifier = "wallet";
    
    /**
     * Constructs a wallet with a specific amount of money.
     *
     * @param amount The amount of money
     */
    public Wallet(int amount)
    {
        this.amount = amount;
    }
    
    /**
     * Creates a wallet from an entity.
     *
     * @param entity The wallet entity
     * @return The new wallet, or <i>null</i> if the entity is not a wallet
     */
    public static Wallet create(Entity entity)
    {
        if(!(entity instanceof Item))
            return null;
        
        ItemStack item = ((Item) entity).getItemStack();
        
        if(item.getType() != Material.WRITTEN_BOOK)
            return null;
        
        BookMeta book = ((BookMeta) item.getItemMeta());
        if(!book.getAuthor().equals(identifier))
            return null;
        
        String amount = book.getTitle().replaceAll("[^\\d]", "");
        return new Wallet(Integer.parseUnsignedInt(amount));
    }
    
    /**
     * Drops the wallet in the player's inventory (or on the ground if the player is dead).
     *
     * @param player The player dropping the wallet
     */
    public void drop(Player player)
    {
        try
        {
            Database.addMoney(player, -amount);
        }
        catch(SQLException e)
        {
            MCRRP.error(e, player);
        }
        
        ItemStack item = new ItemStack(Material.WRITTEN_BOOK);
        
        BookMeta book = (BookMeta) item.getItemMeta();
        book.setAuthor(identifier);
        book.setTitle("$" + amount);
        book.addPage(tr("This wallet contains") + " $" + amount + ".");
        item.setItemMeta(book);
    
        if(player.isDead())
            player.getWorld().dropItemNaturally(player.getLocation(), item);
        
        else
        {
            player.getInventory().addItem(item);
            player.playSound(player.getLocation(), Sound.ENTITY_ITEM_PICKUP, 1, 1);
        }
    }
    
    /**
     * Picks up the wallet and add the money to the player's balance.
     *
     * @param player The player picking up the wallet
     */
    public void pickup(Player player)
    {
        try
        {
            Database.addMoney(player, amount);
        }
        catch(SQLException e)
        {
            MCRRP.error(e, player);
        }
        
        player.playSound(player.getLocation(), Sound.ENTITY_ITEM_PICKUP, 1, 1);
    }
    
    /**
     * Returns the amount of money in the wallet.
     *
     * @return The amount of money
     */
    public int amount()
    {
        return amount;
    }
}
