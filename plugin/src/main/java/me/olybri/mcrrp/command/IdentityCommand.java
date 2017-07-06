package me.olybri.mcrrp.command;// Created by Loris Witschard on 7/6/2017.

import me.olybri.mcrrp.Database;
import me.olybri.mcrrp.Message;
import me.olybri.mcrrp.Tr;

import java.sql.ResultSet;
import java.util.List;

public class IdentityCommand extends PlayerCommand
{
    public IdentityCommand()
    {
        super(0, true);
    }
    
    @Override
    protected boolean run(ResultSet citizen, List<String> args) throws Exception
    {
        ResultSet state = Database.state(citizen.getInt("state_id"));
        Message msg = new Message("",
            Tr.s("First name(s)") + ": {value:" + citizen.getString("first_name") + "}\n"
                + Tr.s("Last name(s)") + ": {value:" + citizen.getString("last_name") + "}\n"
                + Tr.s("Code") + ": {value:" + citizen.getString("code") + "}\n"
                + Tr.s("Sex") + ": {value:" + citizen.getString("sex") + "}\n"
                + Tr.s("State") + ": {value:" + state.getString("name") + "}");
        
        setMessage(msg);
        return true;
    }
}
