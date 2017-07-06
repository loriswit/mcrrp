package me.olybri.mcrrp.command;// Created by Loris Witschard on 7/6/2017.

import me.olybri.mcrrp.Message;
import me.olybri.mcrrp.Tr;
import me.olybri.mcrrp.interaction.SellInteraction;

import java.sql.ResultSet;
import java.util.List;

public class SellCommand extends PlayerCommand
{
    public SellCommand()
    {
        super(2, false);
    }
    
    @Override
    protected boolean run(ResultSet citizen, List<String> args) throws Exception
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
