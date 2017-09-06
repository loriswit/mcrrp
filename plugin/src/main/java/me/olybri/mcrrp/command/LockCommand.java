package me.olybri.mcrrp.command;// Created by Loris Witschard on 9/6/2017.

import me.olybri.mcrrp.interaction.LockInteraction;
import me.olybri.mcrrp.util.Message;
import org.bukkit.entity.Player;

import java.util.List;

import static me.olybri.mcrrp.util.Translation.tr;

/**
 * Command that locks a block (door, chest, etc.) so that only some players can interact with.
 */
public class LockCommand extends PlayerCommand
{
    /**
     * Constructs the lock command.
     */
    public LockCommand()
    {
        super(1, false);
    }
    
    @Override
    protected boolean run(Player player, List<String> args) throws Exception
    {
        String name = String.join(" ", args);
        setInteraction(new LockInteraction(name));
        setMessage(new Message(tr("Please click on any lockable block") + "..."));
        
        return true;
    }
}
