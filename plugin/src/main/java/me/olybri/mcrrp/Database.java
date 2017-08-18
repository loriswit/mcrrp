package me.olybri.mcrrp;// Created by Loris Witschard on 6/11/2017.

import org.bukkit.entity.Player;

import java.sql.*;

/**
 * Class that provides static functions to connect and communicate with the MCRRP database.
 */
public class Database
{
    private static Connection conn;
    
    /**
     * Initializes the connection to the MCRRP database.
     *
     * @throws SQLException if a database error occurs
     */
    public static void init() throws SQLException
    {
        String name = MCRRP.config.getString("database.name");
        String user = MCRRP.config.getString("database.user");
        String pass = MCRRP.config.getString("database.pass");
        
        String url = "jdbc:mysql://localhost/" + name;
        conn = DriverManager.getConnection(url, user, pass);
    }
    
    /**
     * Returns a citizen record.
     *
     * @param id The ID of a valid citizen
     * @return An array containing all fields of the record
     */
    public static ResultSet citizen(int id) throws SQLException
    {
        PreparedStatement statement = conn.prepareStatement("SELECT * FROM citizen WHERE id = ?");
        statement.setInt(1, id);
        return result(statement);
    }
    
    /**
     * Returns the citizen record associated with a player.
     *
     * @param player The player associated to the citizen
     * @return An array containing all fields of the record
     */
    public static ResultSet citizen(Player player) throws SQLException
    {
        PreparedStatement statement = conn.prepareStatement("SELECT * FROM citizen WHERE player = ?");
        statement.setString(1, player.getUniqueId().toString());
        return result(statement);
    }
    
    /**
     * Returns a state record.
     *
     * @param stateID The ID of a valid state
     * @return An array containing all fields of the record
     */
    public static ResultSet state(int stateID) throws SQLException
    {
        PreparedStatement statement = conn.prepareStatement("SELECT * FROM state WHERE id = ?");
        statement.setInt(1, stateID);
        return result(statement);
    }
    
    /**
     * Adds a transaction to the database.
     *
     * @param buyerID     The ID of the buyer
     * @param sellerID    The ID of the seller
     * @param amount      The amount of money transacted
     * @param description A textual description of the transaction
     */
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
    
    /**
     * Executes a SQL query and returns the result set.
     *
     * @param statement The prepared SQL statement to execute
     * @return The result set of the query with cursor on the first row
     */
    private static ResultSet result(PreparedStatement statement) throws SQLException
    {
        ResultSet rs = statement.executeQuery();
        rs.first();
        return rs;
    }
}
