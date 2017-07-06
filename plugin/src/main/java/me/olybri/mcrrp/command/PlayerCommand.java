package me.olybri.mcrrp.command;// Created by Loris Witschard on 7/5/2017.

import me.olybri.mcrrp.Database;
import me.olybri.mcrrp.MCRRP;
import me.olybri.mcrrp.Message;
import me.olybri.mcrrp.Tr;
import me.olybri.mcrrp.interaction.Interaction;
import me.olybri.mcrrp.interaction.ShowMessageInteraction;
import me.olybri.mcrrp.listener.InteractionListener;
import org.bukkit.command.Command;
import org.bukkit.command.CommandExecutor;
import org.bukkit.command.CommandSender;
import org.bukkit.entity.Player;

import java.sql.ResultSet;
import java.util.Arrays;
import java.util.List;

public abstract class PlayerCommand implements CommandExecutor
{
    private Message message = new Message();
    private Interaction interaction;
    
    private int argsCount;
    private boolean canShow;
    
    public PlayerCommand(int argsCount, boolean canShow)
    {
        this.argsCount = argsCount;
        this.canShow = canShow;
    }
    
    @Override
    public final boolean onCommand(CommandSender sender, Command command, String label, String[] args)
    {
        if(!(sender instanceof Player))
        {
            sender.sendMessage(Tr.s("This command can only be used by a player") + ".");
            return true;
        }
        
        Player player = (Player) sender;
        return apply(player, label, Arrays.asList(args), false);
    }
    
    public final boolean apply(Player player, String label, List<String> args, boolean show)
    {
        if(show && !canShow)
        {
            new Message(Tr.s("Cannot show command") + ": {value:" + label + "}.").send(player);
            return true;
        }
        
        if(args.size() < argsCount)
            return false;
        
        try
        {
            message = new Message();
            interaction = null;
            
            ResultSet citizen = Database.citizen(player);
            boolean success = run(citizen, args);
            if(success)
            {
                if(show)
                {
                    interaction = new ShowMessageInteraction(message.body);
                    message = new Message(Tr.s("Please click on any player") + "...");
                }
                
                message.send(player);
                if(!label.equals("show"))
                    InteractionListener.putInteraction(player, interaction);
            }
            return success;
        }
        catch(Exception e)
        {
            MCRRP.error(e, player);
        }
        
        return true;
    }
    
    protected final void setMessage(Message message)
    {
        this.message = message;
    }
    
    protected final void setInteraction(Interaction interaction)
    {
        this.interaction = interaction;
    }
    
    protected abstract boolean run(ResultSet citizen, List<String> args) throws Exception;
}
