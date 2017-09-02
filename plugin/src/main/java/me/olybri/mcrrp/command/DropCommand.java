package me.olybri.mcrrp.command;// Created by Loris Witschard on 8/25/2017.

import me.olybri.mcrrp.util.Database;
import me.olybri.mcrrp.util.Message;
import me.olybri.mcrrp.util.Wallet;
import org.bukkit.entity.Player;

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
        
        new Wallet(amount).drop(player);
        setMessage(new Message(tr("Dropped") + " {value:$" + amount + "}."));
        
        return true;
    }
}
