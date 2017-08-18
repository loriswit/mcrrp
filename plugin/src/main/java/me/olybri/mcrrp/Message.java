package me.olybri.mcrrp;// Created by Loris Witschard on 7/4/2017.

import org.bukkit.ChatColor;
import org.bukkit.entity.Player;

/**
 * Class representing a message that can be sent to a player.
 * The following tags will be printed in a specific format:
 * {name:<i>string</i>} for citizens names ;
 * {value:<i>string</i>} for values.
 */
public class Message
{
    /**
     * The title of the message.
     */
    public String title = "";
    
    /**
     * The body of the message.
     */
    public String body = "";
    
    /**
     * Constructs a message with title and body.
     *
     * @param title The title of the message
     * @param body  The body of the message
     */
    public Message(String title, String body)
    {
        this.title = title;
        this.body = body;
    }
    
    /**
     * Constructs a message with title only.
     *
     * @param title The title of the message
     */
    public Message(String title)
    {
        this.title = title;
    }
    
    /**
     * Constructs an empty message.
     */
    public Message()
    {
    }
    
    /**
     * Sends the message to a specific player.
     *
     * @param player The target player
     */
    public void send(Player player)
    {
        if(title.isEmpty() && body.isEmpty())
            return;
        
        String message = title;
        if(!message.isEmpty())
            message += "\n";
        
        message += body.replaceAll("(?m)^", "  ") + "\n ";
        
        message = message.replaceAll("\\{name:([^}]+)}", ChatColor.GREEN + "$1" + ChatColor.RESET);
        message = message.replaceAll("\\{value:([^}]+)}", ChatColor.YELLOW + "$1" + ChatColor.RESET);
        
        player.sendMessage(message);
    }
}
