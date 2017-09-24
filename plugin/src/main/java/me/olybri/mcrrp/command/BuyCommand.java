package me.olybri.mcrrp.command;// Created by Loris Witschard on 7/7/2017.

import me.olybri.mcrrp.util.Database;
import me.olybri.mcrrp.util.ItemName;
import me.olybri.mcrrp.util.Message;
import org.bukkit.Location;
import org.bukkit.Material;
import org.bukkit.block.Block;
import org.bukkit.block.Chest;
import org.bukkit.entity.Player;
import org.bukkit.inventory.Inventory;
import org.bukkit.inventory.ItemStack;

import java.sql.ResultSet;
import java.util.List;

import static me.olybri.mcrrp.util.Translation.tr;

/**
 * Command that allows a player to buy something from a chest of another player.
 * This command cannot be used directly in the player chat.
 */
public class BuyCommand extends PlayerCommand
{
    /**
     * Constructs the buy command.
     */
    public BuyCommand()
    {
        super(7, false, true);
    }
    
    @Override
    protected boolean run(Player player, List<String> args) throws Exception
    {
        double x;
        double y;
        double z;
        int amount;
        short dataValue;
        int price;
        int sellerID;
        
        try
        {
            x = Double.parseDouble(args.get(0));
            y = Double.parseDouble(args.get(1));
            z = Double.parseDouble(args.get(2));
            amount = Integer.parseUnsignedInt(args.get(4));
            dataValue = Short.parseShort(args.get(5));
            price = Integer.parseUnsignedInt(args.get(6));
            sellerID = Integer.parseUnsignedInt(args.get(7));
        }
        catch(NumberFormatException e)
        {
            return false;
        }
        
        Block block = new Location(player.getWorld(), x, y, z).getBlock();
        if(!(block.getState() instanceof Chest))
        {
            setMessage(new Message(tr("Invalid chest") + "."));
            return true;
        }
        
        Material material = Material.getMaterial(args.get(3));
        if(material == null)
        {
            setMessage(new Message(tr("Invalid article") + "."));
            return true;
        }
        
        if(!Database.citizen(sellerID).first())
        {
            setMessage(new Message(tr("Invalid seller") + "."));
            return true;
        }
        
        ResultSet citizen = Database.citizen(player);
        if(citizen.getInt("id") == sellerID)
        {
            setMessage(new Message(tr("Cannot buy what your are selling") + "."));
            return true;
        }
        
        if(citizen.getInt("balance") < price)
        {
            setMessage(new Message(tr("Your balance is too low") + "."));
            return true;
        }
        
        ItemStack item = new ItemStack(material, amount, dataValue);
        Inventory chestInventory = ((Chest) block.getState()).getInventory();
        Inventory playerInventory = player.getInventory();
        
        ItemStack notRemoved = chestInventory.removeItem(item.clone()).get(0);
        if(notRemoved != null)
        {
            notRemoved.setAmount(amount - notRemoved.getAmount());
            chestInventory.addItem(notRemoved);
            setMessage(new Message(tr("Not enough articles left") + "."));
            return true;
        }
        
        ItemStack notAdded = playerInventory.addItem(item.clone()).get(0);
        if(notAdded != null)
        {
            notAdded.setAmount(amount - notAdded.getAmount());
            playerInventory.removeItem(notAdded);
            chestInventory.addItem(item);
            setMessage(new Message(tr("Not enough space in your inventory") + "."));
            return true;
        }
        
        int buyerID = citizen.getInt("id");
        Database.addTransaction(buyerID, sellerID, price,
            "Bought " + amount + " :" + item.getType().name() + "." + item.getDurability() + ": @ $" + price);
        
        setMessage(new Message(tr("Bought")
            + " {value:" + amount + " " + new ItemName(item) + "} @ $" + price + "."));
        
        return true;
    }
}
