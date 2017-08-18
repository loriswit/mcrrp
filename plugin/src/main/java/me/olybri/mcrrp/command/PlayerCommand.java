package me.olybri.mcrrp.command;// Created by Loris Witschard on 7/5/2017.

import me.olybri.mcrrp.MCRRP;
import me.olybri.mcrrp.Message;
import me.olybri.mcrrp.Tr;
import me.olybri.mcrrp.interaction.Interaction;
import me.olybri.mcrrp.interaction.ShowMessageInteraction;
import me.olybri.mcrrp.listener.InteractionListener;
import org.bukkit.command.Command;
import org.bukkit.command.CommandExecutor;
import org.bukkit.command.CommandSender;
import org.bukkit.command.ProxiedCommandSender;
import org.bukkit.entity.Player;

import java.util.Arrays;
import java.util.List;

/**
 * Abstract class representing a command executable by a player.
 */
public abstract class PlayerCommand implements CommandExecutor
{
    private Message message = new Message();
    private Interaction interaction;
    
    private int argsCount;
    private boolean canShow;
    
    /**
     * Construct the player command.
     *
     * @param argsCount The number of arguments needed to execute the command
     * @param canShow   <i>true</i> if result of the command can be shown to another player, <i>false</i> if not
     */
    public PlayerCommand(int argsCount, boolean canShow)
    {
        this.argsCount = argsCount;
        this.canShow = canShow;
    }
    
    @Override
    public final boolean onCommand(CommandSender sender, Command command, String label, String[] args)
    {
        Player player;
        
        if(sender instanceof Player)
            player = (Player) sender;
        
        else if(sender instanceof ProxiedCommandSender
            && ((ProxiedCommandSender) sender).getCallee() instanceof Player)
            player = (Player) ((ProxiedCommandSender) sender).getCallee();
        
        else
        {
            sender.sendMessage(Tr.s("This command can only be used by a player") + ".");
            return true;
        }
        
        return apply(player, label, Arrays.asList(args), false);
    }
    
    /**
     * Executes the command.
     *
     * @param player The player executing the command
     * @param label  The label used to execute the command
     * @param args   The arguments list
     * @param show   <i>true</i> if the result of the command has to be shown to another player, <i>false</i> if not
     * @return
     */
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
            
            boolean success = run(player, args);
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
    
    /**
     * Defines the result message of the command.
     *
     * @param message The result message
     */
    protected final void setMessage(Message message)
    {
        this.message = message;
    }
    
    /**
     * Defines the interaction to listen to once the command has been executed.
     *
     * @param interaction The interaction to listen to
     */
    protected final void setInteraction(Interaction interaction)
    {
        this.interaction = interaction;
    }
    
    /**
     * Runs the command process.
     *
     * @param player The player executing the command
     * @param args   The arguments list
     * @return <i>true</i> if the process succeeded, <i>false</i> if not
     */
    protected abstract boolean run(Player player, List<String> args) throws Exception;
}
