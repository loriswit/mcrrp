package me.olybri.mcrrp;// Created by Loris Witschard on 6/11/2017.

import me.olybri.mcrrp.command.*;
import me.olybri.mcrrp.listener.CommandListener;
import me.olybri.mcrrp.listener.DeathListener;
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

/**
 * Minecraft Realistic Roleplay
 * Main Bukkit plugin
 */
public final class MCRRP extends JavaPlugin
{
    private static MCRRP instance;
    
    public static FileConfiguration config = new YamlConfiguration();
    
    @Override
    public void onEnable()
    {
        if(System.getProperty("me.olybri.mcrrp.setup") != null)
        {
            getLogger().info("MCRRP is being installed. Shutting down server.");
            Bukkit.getPluginManager().disablePlugin(this);
            Bukkit.shutdown();
            return;
        }
        
        instance = this;
        
        try
        {
            config.load(new File("../config.yml"));
            
            Database.init();
            Tr.init();
        }
        catch(Exception e)
        {
            printException(e);
            getLogger().severe("Failed to enable MCRRP. Shutting down server.");
            Bukkit.getPluginManager().disablePlugin(this);
            Bukkit.shutdown();
            return;
        }
        
        getLogger().info("Registering listeners...");
        getServer().getPluginManager().registerEvents(new LoginListener(), this);
        getServer().getPluginManager().registerEvents(new CommandListener(), this);
        getServer().getPluginManager().registerEvents(new InteractionListener(), this);
        getServer().getPluginManager().registerEvents(new DeathListener(), this);
        
        getLogger().info("Registering command executors...");
        getCommand("identity").setExecutor(new IdentityCommand());
        getCommand("balance").setExecutor(new BalanceCommand());
        getCommand("show").setExecutor(new ShowCommand());
        getCommand("sell").setExecutor(new SellCommand());
        getCommand("buy").setExecutor(new BuyCommand());
        
        getLogger().info("Plugin enabled successfully.");
    }
    
    /**
     * Returns one of the registered command from this plugin.
     *
     * @param name The command name
     * @return The command associated to the name
     */
    public static PluginCommand command(String name)
    {
        PluginCommand command = instance.getCommand(name);
        if(command == null || command.getExecutor() == null)
            return null;
        
        return command;
    }
    
    /**
     * Prints an fatal error and kicks the player causing it.
     *
     * @param exception The exception to be printed
     * @param player    The player causing the exception
     * @return The kick message sent to the player
     */
    public static String error(Exception exception, Player player)
    {
        String msg = printException(exception) + ".\n" + Tr.s("Please contact an administrator") + ".";
        Bukkit.getScheduler().runTask(instance, () -> player.kickPlayer(msg));
        
        return msg;
    }
    
    /**
     * Prints an exception message to the log.
     *
     * @param exception The exception to print
     * @return The printed message
     */
    private static String printException(Exception exception)
    {
        String title = Tr.s("An error occurred") + ": " + exception.getClass().getSimpleName();
        instance.getLogger().severe(title);
        instance.getLogger().severe(Tr.s("Message") + ": " + exception.getMessage());
        
        StringWriter stackTrace = new StringWriter();
        exception.printStackTrace(new PrintWriter(stackTrace));
        instance.getLogger().severe(stackTrace.toString());
        
        return title;
    }
}
