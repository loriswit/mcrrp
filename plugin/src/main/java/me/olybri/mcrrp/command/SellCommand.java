package me.olybri.mcrrp.command;// Created by Loris Witschard on 7/6/2017.

import me.olybri.mcrrp.interaction.SellInteraction;
import me.olybri.mcrrp.util.Message;
import org.bukkit.entity.Player;

import java.util.List;

import static me.olybri.mcrrp.util.Translation.tr;

/**
 * Command that allows a player to sell the content of a chest.
 */
public class SellCommand extends PlayerCommand
{
    /**
     * Constructs the sell command.
     */
    public SellCommand()
    {
        super(2, false);
    }
    
    @Override
    protected boolean run(Player player, List<String> args) throws Exception
    {
        int amount;
        int price;
        
        try
        {
            amount = Integer.parseUnsignedInt(args.get(0));
            price = Integer.parseUnsignedInt(args.get(1));
        }
        catch(NumberFormatException e)
        {
            return false;
        }
        
        setInteraction(new SellInteraction(amount, price));
        setMessage(new Message(tr("Please click on any chest") + "..."));
        
        return true;
    }
}
