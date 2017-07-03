package me.olybri.mcrrp;// Created by Loris Witschard on 6/11/2017.

import org.bukkit.Bukkit;
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
    }
    
    @Override
    public void onDisable()
    {
        // TODO Insert logic to be performed when the plugin is disabled
    }
    
    public static void kickPlayer(final Player player, final String msg)
    {
        Bukkit.getScheduler().runTask(instance, () -> player.kickPlayer(msg));
    }
}
