package me.olybri.mcrrp;// Created by Loris Witschard on 6/11/2017.

import me.olybri.mcrrp.command.*;
import me.olybri.mcrrp.listener.CommandListener;
import me.olybri.mcrrp.listener.InteractionListener;
import me.olybri.mcrrp.listener.LoginListener;
import org.bukkit.Bukkit;
import org.bukkit.command.PluginCommand;
import org.bukkit.configuration.file.FileConfiguration;
import org.bukkit.configuration.file.YamlConfiguration;
import org.bukkit.entity.Player;
import org.bukkit.plugin.java.JavaPlugin;

import java.io.File;
import java.io.PrintWriter;
import java.io.StringWriter;

public final class MCRRP extends JavaPlugin
{
    private static MCRRP instance;
    
    public static FileConfiguration config = new YamlConfiguration();
    
    @Override
    public void onEnable()
    {
        instance = this;
        
        try
        {
            config.load(new File("../config.yml"));
            
            Database.init();
            Tr.init();
        }
        catch(Exception e)
        {
            getLogger().severe(e.getMessage());
            Bukkit.shutdown();
        }
        
        getServer().getPluginManager().registerEvents(new LoginListener(), this);
        getServer().getPluginManager().registerEvents(new CommandListener(), this);
        getServer().getPluginManager().registerEvents(new InteractionListener(), this);
        
        getCommand("identity").setExecutor(new IdentityCommand());
        getCommand("balance").setExecutor(new BalanceCommand());
        getCommand("show").setExecutor(new ShowCommand());
        getCommand("sell").setExecutor(new SellCommand());
        getCommand("buy").setExecutor(new BuyCommand());
    }
    
    public static String error(Exception exception, Player player)
    {
        instance.getLogger().severe(exception.getClass().getSimpleName() + ": " + exception.getMessage());
        
        StringWriter stackTrace = new StringWriter();
        exception.printStackTrace(new PrintWriter(stackTrace));
        instance.getLogger().severe(stackTrace.toString());
        
        String msg = Tr.s("An error occurred") + ": " + exception.getClass().getSimpleName() + ".\n"
            + Tr.s("Please contact an administrator") + ".";
        
        Bukkit.getScheduler().runTask(instance, () -> player.kickPlayer(msg));
        
        return msg;
    }
    
    public static PluginCommand command(String name)
    {
        PluginCommand command = instance.getCommand(name);
        if(command == null || command.getExecutor() == null)
            return null;
        
        return command;
    }
}
