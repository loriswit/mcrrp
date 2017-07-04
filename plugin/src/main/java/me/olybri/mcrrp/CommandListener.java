package me.olybri.mcrrp;// Created by Loris Witschard on 6/11/2017.

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
        Entity entity = event.getRightClicked();
        if(!(entity instanceof Player))
            return;
        
        Player player = event.getPlayer();
        UUID uuid = player.getUniqueId();
        
        if(!messageWaiting.containsKey(uuid))
            return;
        
        Player target = (Player) entity;
        
        String message = messageWaiting.remove(uuid);
        
        ResultSet citizen = Database.citizen(player);
        String name = citizen.getString("first_name") + " " + citizen.getString("last_name");
        
        String title = "{name:" + name + "} " + Tr.s("to") + " {name:" + Tr.s("you") + "}:";
        new Message(title, message).send(target);
        
        title = "{name:" + Tr.s("You") + "} " + Tr.s("showed") + ":";
        new Message(title, message).send(player);
    }
    
    private void runCommand(Player player, String commandLine) throws SQLException
    {
        commandLine = commandLine.replaceAll("\\s", " ").trim().toLowerCase();
        String[] args = commandLine.split(" ");
        
        boolean show = args[0].equals("show");
        String command = show ? args[1] : args[0];
        
        messageWaiting.remove(player.getUniqueId());
        
        Message message = new Message();
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
                break;
        }
        
        if(show)
        {
            messageWaiting.put(player.getUniqueId(), message.body);
            new Message(Tr.s("Please click on any player") + "...").send(player);
        }
        else
            message.send(player);
    }
}
