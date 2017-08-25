package me.olybri.mcrrp.command;// Created by Loris Witschard on 7/6/2017.

import me.olybri.mcrrp.util.Database;
import me.olybri.mcrrp.util.Message;
import org.bukkit.entity.Player;

import java.sql.ResultSet;
import java.util.List;

import static me.olybri.mcrrp.util.Translation.tr;

/**
 * Command that prints the player's balance
 */
public class BalanceCommand extends PlayerCommand
{
    /**
     * Constructs the balance command.
     */
    public BalanceCommand()
    {
        super(0, true);
    }
    
    @Override
    protected boolean run(Player player, List<String> args) throws Exception
    {
        ResultSet citizen = Database.citizen(player);
        Message msg = new Message("",
            tr("Current balance") + ": {value:" + (citizen.getInt("balance")) + "}");
        
        setMessage(msg);
        return true;
    }
}
