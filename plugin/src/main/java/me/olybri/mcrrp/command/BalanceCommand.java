package me.olybri.mcrrp.command;// Created by Loris Witschard on 7/6/2017.

import me.olybri.mcrrp.Database;
import me.olybri.mcrrp.Message;
import me.olybri.mcrrp.Tr;
import org.bukkit.entity.Player;

import java.sql.ResultSet;
import java.util.List;

public class BalanceCommand extends PlayerCommand
{
    public BalanceCommand()
    {
        super(0, true);
    }
    
    @Override
    protected boolean run(Player player, List<String> args) throws Exception
    {
        ResultSet citizen = Database.citizen(player);
        Message msg = new Message("",
            Tr.s("Current balance") + ": {value:" + (citizen.getInt("balance")) + "}");
    
        setMessage(msg);
        return true;
    }
}
