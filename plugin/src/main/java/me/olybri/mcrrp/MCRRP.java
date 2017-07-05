package me.olybri.mcrrp;// Created by Loris Witschard on 6/11/2017.

import me.olybri.mcrrp.listener.CommandListener;
import me.olybri.mcrrp.listener.InteractionListener;
import me.olybri.mcrrp.listener.LoginListener;
import org.bukkit.Bukkit;
import org.bukkit.entity.Player;
import org.bukkit.plugin.java.JavaPlugin;

import java.io.IOException;
import java.sql.SQLException;
import java.util.logging.Logger;

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
    
    public static Logger log()
    {
        return instance.getLogger();
    }
}
