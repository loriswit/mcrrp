package me.olybri.mcrrp.listener;// Created by Loris Witschard on 6/11/2017.

import me.olybri.mcrrp.Database;
import me.olybri.mcrrp.Message;
import me.olybri.mcrrp.Tr;
import org.bukkit.Bukkit;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.player.PlayerJoinEvent;
import org.bukkit.event.player.PlayerLoginEvent;
import org.bukkit.event.player.PlayerQuitEvent;
import org.bukkit.scoreboard.Scoreboard;
import org.bukkit.scoreboard.Team;

import java.sql.ResultSet;
import java.sql.SQLException;

public class LoginListener implements Listener
{
    @EventHandler
    public void onPlayerLogin(PlayerLoginEvent event) throws SQLException
    {
        if(Database.citizen(event.getPlayer(), false) == null)
        {
            String msg = Tr.s("You first need to register at") + " http://olybri.me";
            event.disallow(PlayerLoginEvent.Result.KICK_WHITELIST, msg);
        }
    }
    
    @EventHandler
    public void onPlayerJoin(PlayerJoinEvent event) throws SQLException
    {
        event.setJoinMessage(null);
        
        Player player = event.getPlayer();
        ResultSet citizen = Database.citizen(player);
        if(citizen == null)
            return;
        
        String name = citizen.getString("first_name") + " " + citizen.getString("last_name");
        new Message(Tr.s("Welcome") + ", {name:" + name + "}").send(player);
    
    
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
    }
}
