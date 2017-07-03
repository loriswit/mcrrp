package me.olybri.mcrrp;// Created by Loris Witschard on 6/11/2017.

import org.bukkit.ChatColor;
import org.bukkit.entity.Entity;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.player.AsyncPlayerChatEvent;
import org.bukkit.event.player.PlayerCommandPreprocessEvent;
import org.bukkit.event.player.PlayerInteractEntityEvent;

import java.sql.ResultSet;
import java.sql.SQLException;
import java.util.HashMap;
import java.util.Map;
import java.util.UUID;

public class CommandListener implements Listener
{
    private Map<UUID, String> messageWaiting = new HashMap<>();
    
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
    
    @EventHandler
    public void onPlayerInteract(PlayerInteractEntityEvent event) throws SQLException
    {
        Entity target = event.getRightClicked();
        if(!(target instanceof Player))
            return;
        
        Player player = event.getPlayer();
        UUID uuid = player.getUniqueId();
        
        if(!messageWaiting.containsKey(uuid))
            return;
        
        String message = messageWaiting.remove(uuid);
        
        ResultSet citizen = Database.citizen(player);
        String name = citizen.getString("first_name") + " " + citizen.getString("last_name");
        
        target.sendMessage(nameColor(name) + " " + Tr.s("to") + " " + nameColor(Tr.s("you")));
        target.sendMessage(formatMessage(message));
        
        citizen = Database.citizen((Player) target);
        name = citizen.getString("first_name") + " " + citizen.getString("last_name");
        
        player.sendMessage(nameColor(Tr.s("You")) + " " + Tr.s("showed") + ":\n");
        player.sendMessage(formatMessage(message));
    }
    
    private void runCommand(Player player, String commandLine) throws SQLException
    {
        commandLine = commandLine.replaceAll("\\s", " ").trim().toLowerCase();
        String[] args = commandLine.split(" ");
        
        boolean show = args[0].equals("show");
        String command = show ? args[1] : args[0];
        
        messageWaiting.remove(player.getUniqueId());
        
        String message;
        ResultSet citizen = Database.citizen(player);
        
        switch(command)
        {
            case "identity":
            case "id":
                ResultSet state = Database.state(citizen.getInt("state_id"));
                message = Tr.s("First name(s)") + ": " + valueColor(citizen.getString("first_name"))
                        + "\n" + Tr.s("Last name(s)") + ": " + valueColor(citizen.getString("last_name"))
                        + "\n" + Tr.s("Code") + ": " + valueColor(citizen.getString("code"))
                        + "\n" + Tr.s("Sex") + ": " + valueColor(citizen.getString("sex"))
                        + "\n" + Tr.s("State") + ": " + valueColor(state.getString("name"));
                break;
            
            case "balance":
            case "bal":
            case "$":
                message = Tr.s("Current balance") + ": " + valueColor(citizen.getInt("balance"));
                break;
            
            default:
                player.sendMessage(Tr.s("Unknown command") + ": " + command + "\n ");
                return;
        }
        
        if(show)
        {
            messageWaiting.put(player.getUniqueId(), message);
            player.sendMessage(Tr.s("Please click on any player") + "...");
        }
        else
            player.sendMessage(formatMessage(message));
    }
    
    private String formatMessage(String message)
    {
        return message.replaceAll("(?m)^", "  ") + "\n ";
    }
    
    private String nameColor(String str)
    {
        return ChatColor.GREEN + str + ChatColor.RESET;
    }
    
    private String valueColor(String str)
    {
        return ChatColor.YELLOW + str + ChatColor.RESET;
    }
    
    private String valueColor(int value)
    {
        return valueColor(Integer.toString(value));
    }
}
