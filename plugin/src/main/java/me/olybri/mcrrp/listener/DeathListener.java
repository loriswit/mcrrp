package me.olybri.mcrrp.listener;// Created by Loris Witschard on 8/22/2017.

import me.olybri.mcrrp.MCRRP;
import me.olybri.mcrrp.util.Database;
import org.bukkit.ChatColor;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.entity.PlayerDeathEvent;

import java.nio.file.Files;
import java.nio.file.Paths;
import java.sql.ResultSet;

import static me.olybri.mcrrp.util.Translation.tr;

/**
 * Class listening to player death events.
 */
public class DeathListener implements Listener
{
    @EventHandler
    public void onPlayerDeathEvent(PlayerDeathEvent event)
    {
        Player player = event.getEntity();
        
        try
        {
            ResultSet citizen = Database.citizen(player);
            String name = citizen.getString("first_name") + " " + citizen.getString("last_name");
            
            Database.killCitizen(player);
            
            player.kickPlayer(tr("You died") + ".\n\n"
                + tr("The identity") + " " + ChatColor.YELLOW + name + ChatColor.RESET + "\n"
                + tr("is not linked to your account anymore") + ".");
            
            Files.deleteIfExists(Paths.get("world/playerdata/" + player.getUniqueId() + ".dat"));
        }
        catch(Exception e)
        {
            MCRRP.error(e, player);
        }
    }
}
