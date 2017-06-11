package me.olybri.mcrrp;// Created by Loris Witschard on 6/11/2017.

import org.bukkit.entity.Player;

import java.sql.*;

public class Database
{
    private static Connection conn;
    
    public static void init(String host, String name, String user, String pass) throws SQLException
    {
        String url = "jdbc:mysql://" + host + "/" + name;
        conn = DriverManager.getConnection(url, user, pass);
    }
    
    public static ResultSet citizen(Player player) throws SQLException
    {
        return citizen(player, true);
    }
    
    public static ResultSet citizen(final Player player, boolean kickOnError) throws SQLException
    {
        PreparedStatement statement = conn.prepareStatement("SELECT * FROM citizen WHERE player = ?");
        statement.setString(1, player.getUniqueId().toString());
        ResultSet rs = statement.executeQuery();
        if(!rs.first())
        {
            if(kickOnError)
                MCRRP.kickPlayer(player, "You are not registered on the server.");
            else
                return null;
        }
        return rs;
    }
}
