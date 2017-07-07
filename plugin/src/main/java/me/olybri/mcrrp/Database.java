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
    
    public static ResultSet citizen(int id) throws SQLException
    {
        PreparedStatement statement = conn.prepareStatement("SELECT * FROM citizen WHERE id = ?");
        statement.setInt(1, id);
        return result(statement);
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
        return result(statement);
    }
    
    public static void addTransaction(int buyerID, int sellerID, int amount, String description) throws SQLException
    {
        conn.setAutoCommit(false);
        
        PreparedStatement statement = conn.prepareStatement(
            "INSERT INTO transaction (buyer_id, seller_id, amount, description, timestamp) "
                + "VALUES (?, ?, ?, ?, UNIX_TIMESTAMP(NOW()))");
        
        statement.setInt(1, buyerID);
        statement.setInt(2, sellerID);
        statement.setInt(3, amount);
        statement.setString(4, description);
        statement.executeUpdate();
        
        statement = conn.prepareStatement("UPDATE citizen SET balance = ? WHERE id = ?");
    
        int buyerBalance = citizen(buyerID).getInt("balance") - amount;
        statement.setInt(1, buyerBalance);
        statement.setInt(2, buyerID);
        statement.executeUpdate();
    
        int sellerBalance = citizen(sellerID).getInt("balance") + amount;
        statement.setInt(1, sellerBalance);
        statement.setInt(2, sellerID);
        statement.executeUpdate();
        
        conn.commit();
        conn.setAutoCommit(true);
    }
    
    private static ResultSet result(PreparedStatement statement) throws SQLException
    {
        ResultSet rs = statement.executeQuery();
        rs.first();
        return rs;
    }
}
