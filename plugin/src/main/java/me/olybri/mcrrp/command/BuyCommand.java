package me.olybri.mcrrp.command;// Created by Loris Witschard on 7/7/2017.

import me.olybri.mcrrp.Database;
import me.olybri.mcrrp.Message;
import me.olybri.mcrrp.Tr;
import org.bukkit.Location;
import org.bukkit.Material;
import org.bukkit.block.Block;
import org.bukkit.block.Chest;
import org.bukkit.entity.Player;
import org.bukkit.inventory.Inventory;
import org.bukkit.inventory.ItemStack;

import java.sql.ResultSet;
import java.util.HashMap;
import java.util.List;

// TODO: prevent usage

public class BuyCommand extends PlayerCommand
{
    public BuyCommand()
    {
        super(7, false);
    }
    
    @Override
    protected boolean run(Player player, List<String> args) throws Exception
    {
        double x;
        double y;
        double z;
        int amount;
        int price;
        int sellerID;
        
        try
        {
            x = Double.parseDouble(args.get(0));
            y = Double.parseDouble(args.get(1));
            z = Double.parseDouble(args.get(2));
            amount = Integer.parseInt(args.get(4));
            price = Integer.parseInt(args.get(5));
            sellerID = Integer.parseInt(args.get(6));
        }
        catch(Exception e)
        {
            return false;
        }
        
        Block block = new Location(player.getWorld(), x, y, z).getBlock();
        if(!(block.getState() instanceof Chest))
        {
            setMessage(new Message(Tr.s("Invalid chest") + "."));
            return true;
        }
    
        Material material = Material.matchMaterial(args.get(3));
        if(material == null)
        {
            setMessage(new Message(Tr.s("Invalid article") + "."));
            return true;
        }
        
        if(amount < 1)
        {
            setMessage(new Message(Tr.s("Invalid amount") + "."));
            return true;
        }
    
        if(price < 1)
        {
            setMessage(new Message(Tr.s("Invalid price") + "."));
            return true;
        }
    
        if(!Database.citizen(sellerID).first())
        {
            setMessage(new Message(Tr.s("Invalid seller") + "."));
            return true;
        }
    
        ResultSet citizen = Database.citizen(player);
        if(citizen.getInt("id") == sellerID)
        {
            setMessage(new Message(Tr.s("Cannot buy what your are selling") + "."));
            return true;
        }
        
        if(citizen.getInt("balance") < price)
        {
            setMessage(new Message(Tr.s("Your balance is too low") + "."));
            return true;
        }
        
        Inventory chestInventory = ((Chest) block.getState()).getBlockInventory();
        Inventory playerInventory = player.getInventory();
        
        HashMap<Integer, ItemStack> extra = chestInventory.removeItem(new ItemStack(material, amount));
        if(!extra.isEmpty())
        {
            chestInventory.addItem(new ItemStack(material, amount - extra.get(0).getAmount()));
            setMessage(new Message(Tr.s("Not enough articles left") + "."));
            return true;
        }
        
        extra = playerInventory.addItem(new ItemStack(material, amount));
        if(!extra.isEmpty())
        {
            playerInventory.removeItem(new ItemStack(material, amount - extra.get(0).getAmount()));
            chestInventory.addItem(new ItemStack(material, amount));
            setMessage(new Message(Tr.s("Not enough space in your inventory") + "."));
            return true;
        }
        
        String articleName = material.toString().replace('_', ' ').toLowerCase();
        
        int buyerID = citizen.getInt("id");
        Database.addTransaction(buyerID, sellerID, price, "Bought " + amount + " " + articleName + " @ $" + price);
        
        setMessage(new Message(Tr.s("Bought")
            + " {value:" + amount + " " + articleName + "} @ $" + price + "."));
        
        return true;
    }
}
