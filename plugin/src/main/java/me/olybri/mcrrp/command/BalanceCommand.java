package me.olybri.mcrrp.command;// Created by Loris Witschard on 7/6/2017.

import me.olybri.mcrrp.Message;
import me.olybri.mcrrp.Tr;

import java.sql.ResultSet;
import java.util.List;

public class BalanceCommand extends PlayerCommand
{
    public BalanceCommand()
    {
        super(0, true);
    }
    
    @Override
    protected boolean run(ResultSet citizen, List<String> args) throws Exception
    {
        Message msg = new Message("",
            Tr.s("Current balance") + ": {value:" + (citizen.getInt("balance")) + "}");
    
        setMessage(msg);
        return true;
    }
}
