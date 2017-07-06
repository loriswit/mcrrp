package me.olybri.mcrrp.listener;// Created by Loris Witschard on 6/11/2017.

import me.olybri.mcrrp.MCRRP;
import me.olybri.mcrrp.Message;
import me.olybri.mcrrp.Tr;
import me.olybri.mcrrp.command.PlayerCommand;
import org.bukkit.command.PluginCommand;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.player.AsyncPlayerChatEvent;
import org.bukkit.event.player.PlayerCommandPreprocessEvent;

import java.util.Arrays;
import java.util.LinkedList;
import java.util.List;

public class CommandListener implements Listener
{
    @EventHandler
    public void onPlayerChat(AsyncPlayerChatEvent event)
    {
        event.setCancelled(true);
        runCommand(event.getPlayer(), event.getMessage());
    }
    
    @EventHandler
    public void onPlayerCommand(PlayerCommandPreprocessEvent event)
    {
        event.setCancelled(true);
        runCommand(event.getPlayer(), event.getMessage().substring(1));
    }
    
    private void runCommand(Player player, String commandLine)
    {
        List<String> args = new LinkedList<>(Arrays.asList(commandLine.split("\\s")));
        String name = args.remove(0);
        
        PluginCommand command = MCRRP.command(name);
        
        if(command == null || !(command.getExecutor() instanceof PlayerCommand))
        {
            new Message(Tr.s("Unknown command") + ": {value:" + name + "}").send(player);
            return;
        }
        
        PlayerCommand executor = (PlayerCommand) command.getExecutor();
        if(!executor.apply(player, name, args, false))
            new Message(command.getUsage()).send(player);
    }
}
