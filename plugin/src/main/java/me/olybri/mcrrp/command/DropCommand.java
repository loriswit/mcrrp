package me.olybri.mcrrp.command;// Created by Loris Witschard on 8/25/2017.

import me.olybri.mcrrp.util.Database;
import me.olybri.mcrrp.util.Message;
import org.bukkit.Material;
import org.bukkit.Sound;
import org.bukkit.entity.Player;
import org.bukkit.inventory.ItemStack;
import org.bukkit.inventory.meta.BookMeta;

import java.sql.ResultSet;
import java.util.List;

import static me.olybri.mcrrp.util.Translation.tr;

/**
 * Command that drops a wallet in the player's inventory.
 */
public class DropCommand extends PlayerCommand
{
    /**
     * Constructs the drop command.
     */
    public DropCommand()
    {
        super(1, false);
    }
    
    @Override
    protected boolean run(Player player, List<String> args) throws Exception
    {
        int amount;
        
        try
        {
            amount = Integer.parseUnsignedInt(args.get(0));
        }
        catch(NumberFormatException e)
        {
            return false;
        }
        
        ResultSet citizen = Database.citizen(player);
        if(citizen.getInt("balance") < amount)
        {
            setMessage(new Message(tr("Your balance is too low") + "."));
            return true;
        }
        
        ItemStack item = new ItemStack(Material.WRITTEN_BOOK);
        
        BookMeta book = (BookMeta) item.getItemMeta();
        book.setAuthor("wallet");
        book.setTitle("$" + amount);
        book.addPage(tr("This wallet contains") + " $" + amount + ".");
        item.setItemMeta(book);
        
        Database.addMoney(player, -amount);
        player.getInventory().addItem(item);
        player.playSound(player.getLocation(), Sound.ENTITY_ITEM_PICKUP, 1, 1);
        
        setMessage(new Message(tr("Dropped") + " {value:$" + amount + "}."));
        
        return true;
    }
}
