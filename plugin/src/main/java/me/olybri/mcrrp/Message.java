package me.olybri.mcrrp;// Created by Loris Witschard on 7/4/2017.

import org.bukkit.ChatColor;
import org.bukkit.entity.Player;

public class Message
{
    public String title = "";
    public String body = "";
    
    public Message(String title, String body)
    {
        this.title = title;
        this.body = body;
    }
    
    public Message(String title)
    {
        this.title = title;
    }
    
    public Message()
    {
    }
    
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
