package me.olybri.mcrrp;// Created by Loris Witschard on 6/11/2017.

import me.olybri.mcrrp.command.BalanceCommand;
import me.olybri.mcrrp.command.IdentityCommand;
import me.olybri.mcrrp.command.SellCommand;
import me.olybri.mcrrp.command.ShowCommand;
import me.olybri.mcrrp.listener.CommandListener;
import me.olybri.mcrrp.listener.InteractionListener;
import me.olybri.mcrrp.listener.LoginListener;
import org.bukkit.Bukkit;
import org.bukkit.command.PluginCommand;
import org.bukkit.entity.Player;
import org.bukkit.plugin.java.JavaPlugin;

import java.io.IOException;
import java.io.PrintWriter;
import java.io.StringWriter;
import java.sql.SQLException;

public final class MCRRP extends JavaPlugin
{
    private static MCRRP instance;
    
    @Override
    public void onEnable()
    {
        instance = this;
        
        try
        {
            Database.init("localhost", "mcrrp", "root", "");
            Tr.load("fr");
        }
        catch(SQLException e)
        {
            getLogger().severe(Tr.s("Database connection failed") + ": " + e.getMessage());
        }
        catch(IOException e)
        {
            getLogger().severe(Tr.s("Language file not found") + ": " + e.getMessage());
        }
        
        getServer().getPluginManager().registerEvents(new LoginListener(), this);
        getServer().getPluginManager().registerEvents(new CommandListener(), this);
        getServer().getPluginManager().registerEvents(new InteractionListener(), this);
        
        getCommand("identity").setExecutor(new IdentityCommand());
        getCommand("balance").setExecutor(new BalanceCommand());
        getCommand("show").setExecutor(new ShowCommand());
        getCommand("sell").setExecutor(new SellCommand());
    }
    
    public static String error(Exception exception, Player player)
    {
        instance.getLogger().severe(exception.getClass().getSimpleName() + ": " + exception.getMessage());
        
        StringWriter stackTrace = new StringWriter();
        exception.printStackTrace(new PrintWriter(stackTrace));
        instance.getLogger().info(stackTrace.toString());
        
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
