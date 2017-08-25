package me.olybri.mcrrp.listener;// Created by Loris Witschard on 6/11/2017.

import me.olybri.mcrrp.MCRRP;
import me.olybri.mcrrp.util.Database;
import me.olybri.mcrrp.util.Message;
import org.bukkit.Bukkit;
import org.bukkit.ChatColor;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.player.PlayerJoinEvent;
import org.bukkit.event.player.PlayerLoginEvent;
import org.bukkit.event.player.PlayerQuitEvent;
import org.bukkit.scoreboard.Scoreboard;
import org.bukkit.scoreboard.Team;

import java.sql.ResultSet;

import static me.olybri.mcrrp.util.Translation.tr;

/**
 * Class listening to login/logout related events.
 */
public class LoginListener implements Listener
{
    @EventHandler
    public void onPlayerLogin(PlayerLoginEvent event)
    {
        Player player = event.getPlayer();
        
        try
        {
            if(!Database.citizen(player).first())
            {
                String msg = tr("You first need to register at")
                    + ChatColor.YELLOW + " " + MCRRP.config.getString("settings.website");
                
                event.disallow(PlayerLoginEvent.Result.KICK_WHITELIST, msg);
            }
        }
        catch(Exception e)
        {
            String msg = MCRRP.error(e, player);
            event.disallow(PlayerLoginEvent.Result.KICK_OTHER, msg);
        }
    }
    
    @EventHandler
    public void onPlayerJoin(PlayerJoinEvent event)
    {
        event.setJoinMessage(null);
        Player player = event.getPlayer();
        
        try
        {
            ResultSet citizen = Database.citizen(player);
            
            String name = citizen.getString("first_name") + " " + citizen.getString("last_name");
            new Message(tr("Welcome") + ", {name:" + name + "}").send(player);
        }
        catch(Exception e)
        {
            MCRRP.error(e, player);
            return;
        }
        
        player.setPlayerListName("");
        player.setDisplayName("");
        
        if(!player.hasPlayedBefore())
        {
            Scoreboard board = Bukkit.getScoreboardManager().getMainScoreboard();
            
            Team team;
            if(board.getTeams().isEmpty())
            {
                team = board.registerNewTeam("citizen");
                team.setOption(Team.Option.NAME_TAG_VISIBILITY, Team.OptionStatus.NEVER);
                team.setOption(Team.Option.DEATH_MESSAGE_VISIBILITY, Team.OptionStatus.NEVER);
            }
            else
                team = board.getTeam("citizen");
            
            team.addEntry(player.getName());
        }
    }
    
    @EventHandler
    public void onPlayerQuit(PlayerQuitEvent event)
    {
        event.setQuitMessage(null);
        
        InteractionListener.putInteraction(event.getPlayer(), null);
    }
}
