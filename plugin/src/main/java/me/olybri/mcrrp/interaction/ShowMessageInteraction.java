package me.olybri.mcrrp.interaction;// Created by Loris Witschard on 7/4/2017.

import me.olybri.mcrrp.util.Database;
import me.olybri.mcrrp.util.Message;
import org.bukkit.entity.Player;

import java.sql.ResultSet;

import static me.olybri.mcrrp.util.Translation.tr;

/**
 * Class representing an interaction involving a player showing a message to another one.
 */
public class ShowMessageInteraction extends PlayerInteraction
{
    private String message;
    
    /**
     * Construct the interaction.
     *
     * @param message The message to show
     */
    public ShowMessageInteraction(String message)
    {
        this.message = message;
    }
    
    @Override
    protected boolean run(Player player, Player target) throws Exception
    {
        ResultSet citizen = Database.citizen(player);
        String name = citizen.getString("first_name") + " " + citizen.getString("last_name");
        
        String title = "{name:" + name + "} " + tr("to") + " {name:" + tr("you") + "}:";
        new Message(title, message).send(target);
        
        title = "{name:" + tr("You") + "} " + tr("showed") + ":";
        new Message(title, message).send(player);
        
        return true;
    }
}
