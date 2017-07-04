package me.olybri.mcrrp.listener;// Created by Loris Witschard on 6/11/2017.

import me.olybri.mcrrp.Database;
import me.olybri.mcrrp.Message;
import me.olybri.mcrrp.Tr;
import me.olybri.mcrrp.interaction.Interaction;
import me.olybri.mcrrp.interaction.ShowMessageInteraction;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.player.AsyncPlayerChatEvent;
import org.bukkit.event.player.PlayerCommandPreprocessEvent;

import java.sql.ResultSet;
import java.sql.SQLException;

public class CommandListener implements Listener
{
    @EventHandler
    public void onPlayerChat(AsyncPlayerChatEvent event) throws SQLException
    {
        runCommand(event.getPlayer(), event.getMessage());
        event.setCancelled(true);
    }
    
    @EventHandler
    public void onPlayerCommand(PlayerCommandPreprocessEvent event) throws SQLException
    {
        runCommand(event.getPlayer(), event.getMessage().substring(1));
        event.setCancelled(true);
    }
    
    private void runCommand(Player player, String commandLine) throws SQLException
    {
        commandLine = commandLine.replaceAll("\\s", " ").trim().toLowerCase();
        String[] args = commandLine.split(" ");
        
        boolean show = args[0].equals("show");
        boolean cancelShow = false;
        String command = show ? args[1] : args[0];
        
        Message message = new Message();
        Interaction interaction = null;
        ResultSet citizen = Database.citizen(player);
        
        switch(command)
        {
            case "identity":
            case "id":
                ResultSet state = Database.state(citizen.getInt("state_id"));
                message.body = Tr.s("First name(s)") + ": {value:" + citizen.getString("first_name") + "}\n"
                        + Tr.s("Last name(s)") + ": {value:" + citizen.getString("last_name") + "}\n"
                        + Tr.s("Code") + ": {value:" + citizen.getString("code") + "}\n"
                        + Tr.s("Sex") + ": {value:" + citizen.getString("sex") + "}\n"
                        + Tr.s("State") + ": {value:" + state.getString("name") + "}";
                break;
            
            case "balance":
            case "bal":
            case "$":
                message.body = Tr.s("Current balance") + ": {value:" + (citizen.getInt("balance")) + "}";
                break;
            
            default:
                message.title = Tr.s("Unknown command") + ": {value:" + command + "}";
                show = false;
                break;
        }
        
        if(show)
        {
            if(cancelShow)
            {
                message.title = Tr.s("Cannot show command") + ": {value:" + command + "}";
                interaction = null;
            }
            else
            {
                interaction = new ShowMessageInteraction(message.body);
                message.title = Tr.s("Please click on any player") + "...";
            }
        }
        
        message.send(player);
        InteractionListener.putInteraction(player, interaction);
    }
}
