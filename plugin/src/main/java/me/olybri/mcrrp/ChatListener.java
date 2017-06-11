package me.olybri.mcrrp;// Created by Loris Witschard on 6/11/2017.

import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.player.AsyncPlayerChatEvent;

import java.sql.ResultSet;
import java.sql.SQLException;

public class ChatListener implements Listener
{
    @EventHandler
    public void onPlayerChat(AsyncPlayerChatEvent event) throws SQLException
    {
        if(event.getMessage().equals("$"))
        {
            Player player = event.getPlayer();
            ResultSet rs = Database.citizen(player);
            event.getPlayer().sendMessage("Current balance: " + rs.getInt("balance"));
        }
        
        event.setCancelled(true);
    }
}
