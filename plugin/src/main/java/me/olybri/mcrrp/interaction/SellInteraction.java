package me.olybri.mcrrp.interaction;// Created by Loris Witschard on 7/4/2017.

import me.olybri.mcrrp.Database;
import me.olybri.mcrrp.Message;
import me.olybri.mcrrp.Tr;
import org.bukkit.Bukkit;
import org.bukkit.ChatColor;
import org.bukkit.Material;
import org.bukkit.block.Block;
import org.bukkit.block.BlockFace;
import org.bukkit.block.Sign;
import org.bukkit.entity.Player;
import org.bukkit.inventory.ItemStack;

/**
 * Class representing an interaction with a chest in order to sell its content.
 * The player has to interact with the chest holding the items he wants to sell.
 * The interaction has to be on a vertical face of the chest without any block next to it.
 * If the interaction succeeds, a clickable sign is created at the involved face.
 */
public class SellInteraction extends BlockInteraction
{
    private int amount;
    private int price;
    
    /**
     * Constructs the interaction.
     *
     * @param amount The amount of item to sell for the specified price
     * @param price  The price that the buyer has to pay
     */
    public SellInteraction(int amount, int price)
    {
        this.price = price;
        this.amount = amount;
    }
    
    @Override
    protected boolean run(Player player, Block block, BlockFace face) throws Exception
    {
        if(block.getType() != Material.CHEST && block.getType() != Material.TRAPPED_CHEST)
            return false;
        
        if(!block.getRelative(face).isEmpty())
            return false;
        
        if(face == BlockFace.UP || face == BlockFace.DOWN)
            return false;
        
        if(player.getInventory().getItemInMainHand().getAmount() == 0
            && player.getInventory().getItemInOffHand().getAmount() == 0)
        {
            new Message(Tr.s("There are no items in your hands") + ".").send(player);
            return true;
        }
        
        ItemStack sellingItem = player.getInventory().getItemInMainHand();
        if(sellingItem.getAmount() == 0)
            sellingItem = player.getInventory().getItemInOffHand();
        
        String article = sellingItem.getType().toString();
        
        Block signBlock = block.getRelative(face);
        signBlock.setType(Material.WALL_SIGN);
        Sign sign = (Sign) signBlock.getState();
        
        org.bukkit.material.Sign signMaterial = new org.bukkit.material.Sign(Material.WALL_SIGN);
        signMaterial.setFacingDirection(face);
        
        sign.setData(signMaterial);
        sign.setLine(0, ChatColor.YELLOW + article.replace('_', ' '));
        sign.setLine(1, ChatColor.WHITE + "BUY " + amount + " @ $" + price);
        sign.update();
        
        String chestLocation = block.getLocation().getBlockX() + " "
            + block.getLocation().getBlockY() + " "
            + block.getLocation().getBlockZ() + " ";
        
        String signLocation = signBlock.getLocation().getBlockX() + " "
            + signBlock.getLocation().getBlockY() + " "
            + signBlock.getLocation().getBlockZ() + " ";
        
        int sellerID = Database.citizen(player).getInt("id");
        
        String buyCommand = "buy " + chestLocation + article + " " + amount + " " + price + " " + sellerID;
        String dataTag = "{Text3:\"{'text':'','clickEvent':{'action':'run_command','value':'" + buyCommand + "'}}\"}";
        Bukkit.dispatchCommand(Bukkit.getConsoleSender(), "blockdata " + signLocation + dataTag.replace("'", "\\\""));
        
        new Message(Tr.s("Selling") + " {value:" + amount + " " + article + "} @ $" + price + ".").send(player);
        
        return true;
    }
}
