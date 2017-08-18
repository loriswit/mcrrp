package me.olybri.mcrrp.command;// Created by Loris Witschard on 7/6/2017.

import me.olybri.mcrrp.Message;
import me.olybri.mcrrp.Tr;
import me.olybri.mcrrp.interaction.SellInteraction;
import org.bukkit.entity.Player;

import java.util.List;

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
        try
        {
            setInteraction(new SellInteraction(Integer.parseInt(args.get(0)), Integer.parseInt(args.get(1))));
            setMessage(new Message(Tr.s("Please click on any chest") + "..."));
            return true;
        }
        catch(NumberFormatException e)
        {
            return false;
        }
    }
}
