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
            getLogger().severe("Database connection failed. " + e.getMessage());
            return;
        }
        catch(IOException e)
        {
            getLogger().severe("Language file not found. " + e.getMessage());
            return;
        }
        
        getServer().getPluginManager().registerEvents(new LoginListener(), this);
        getServer().getPluginManager().registerEvents(new CommandListener(), this);
        getServer().getPluginManager().registerEvents(new InteractionListener(), this);
        
        getCommand("identity").setExecutor(new IdentityCommand());
        getCommand("balance").setExecutor(new BalanceCommand());
        getCommand("show").setExecutor(new ShowCommand());
        getCommand("sell").setExecutor(new SellCommand());
    }
    
    public static void kickPlayer(final Player player, final String msg)
    {
        Bukkit.getScheduler().runTask(instance, () -> player.kickPlayer(msg));
    }
    
    public static void error(Exception excepton, Player player)
    {
        new Message(Tr.s("An error occurred") + ": {value:" + excepton.getClass().getSimpleName() + "}.").send(player);
        instance.getLogger().severe(excepton.getMessage());
        excepton.printStackTrace();
    }
    
    public static PluginCommand command(String name)
    {
        PluginCommand command = instance.getCommand(name);
        if(command == null || command.getExecutor() == null)
            return null;
        
        return command;
    }
}
