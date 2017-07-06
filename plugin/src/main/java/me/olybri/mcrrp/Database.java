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
        PreparedStatement statement = conn.prepareStatement("SELECT * FROM citizen WHERE player = ?");
        statement.setString(1, player.getUniqueId().toString());
        ResultSet rs = statement.executeQuery();
        rs.first();
        return rs;
    }
    
    public static ResultSet state(int stateID) throws SQLException
    {
        PreparedStatement statement = conn.prepareStatement("SELECT * FROM state WHERE id = ?");
        statement.setInt(1, stateID);
        ResultSet rs = statement.executeQuery();
        rs.first();
        return rs;
    }
}
