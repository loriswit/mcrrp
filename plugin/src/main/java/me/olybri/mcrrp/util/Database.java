package me.olybri.mcrrp.util;// Created by Loris Witschard on 6/11/2017.

import me.olybri.mcrrp.MCRRP;
import org.bukkit.block.Block;
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
     * Tells if a block is locked.
     *
     * @param block The lockable block
     * @return <i>true</i> if the block is locked, <i>false</i> if not
     */
    public static boolean locked(Block block) throws SQLException
    {
        PreparedStatement statement = conn.prepareStatement(
            "SELECT COUNT(*) AS count FROM `lock` WHERE x = ? AND y = ? AND z = ?");
        
        statement.setInt(1, block.getX());
        statement.setInt(2, block.getY());
        statement.setInt(3, block.getZ());
        
        return result(statement).getInt("count") != 0;
    }
    
    /**
     * Tells if a citizen is authorized to interact with a block.
     *
     * @param block  The lockable block
     * @param player The player associated to the citizen
     * @return <i>true</i> if the citizen is authorized to interact, <i>false</i> if not
     */
    public static boolean authorized(Block block, Player player) throws SQLException
    {
        PreparedStatement statement = conn.prepareStatement(
            "SELECT id, owner_id FROM `lock` WHERE x = ? AND y = ? AND z = ?");
        
        statement.setInt(1, block.getX());
        statement.setInt(2, block.getY());
        statement.setInt(3, block.getZ());
        ResultSet lock = statement.executeQuery();
        if(!lock.first())
            return true;
        
        int id = citizen(player).getInt("id");
        if(id == lock.getInt("owner_id"))
            return true;
        
        statement = conn.prepareStatement("SELECT citizen_id FROM authorized WHERE lock_id = ?");
        statement.setInt(1, lock.getInt("id"));
        ResultSet authorized = statement.executeQuery();
        
        while(authorized.next())
            if(id == authorized.getInt("citizen_id"))
                return true;
        
        return false;
    }
    
    /**
     * Locks a block for a specific citizen.
     *
     * @param player The player associated to the citizen
     * @param block  The lockable block
     * @param name   The name of the lock
     */
    public static void lock(Player player, Block block, String name) throws SQLException
    {
        PreparedStatement statement = conn.prepareStatement(
            "INSERT INTO `lock` (owner_id, name, type, x, y, z) "
                + "VALUES (?, ?, ?, ?, ?, ?)");
        
        statement.setInt(1, citizen(player).getInt("id"));
        statement.setString(2, name);
        statement.setString(3, block.getType().name());
        statement.setInt(4, block.getX());
        statement.setInt(5, block.getY());
        statement.setInt(6, block.getZ());
        
        statement.executeUpdate();
    }
    
    /**
     * Unlocks a block and removes all authorizations.
     *
     * @param block The lockable block
     */
    public static void unlock(Block block) throws SQLException
    {
        String[] sqls = {
            "DELETE FROM authorized WHERE lock_id = (SELECT id FROM `lock` WHERE x = ? AND y = ? AND z = ?)",
            "DELETE FROM `lock` WHERE x = ? AND y = ? AND z = ?"
        };
        
        conn.setAutoCommit(false);
        
        for(String sql : sqls)
        {
            PreparedStatement statement = conn.prepareStatement(sql);
            statement.setInt(1, block.getX());
            statement.setInt(2, block.getY());
            statement.setInt(3, block.getZ());
            statement.executeUpdate();
        }
        
        conn.commit();
        conn.setAutoCommit(true);
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
        
        statement = conn.prepareStatement("UPDATE citizen SET balance = balance + ? WHERE id = ?");
        
        statement.setInt(1, -amount);
        statement.setInt(2, buyerID);
        statement.executeUpdate();
        
        statement.setInt(1, amount);
        statement.setInt(2, sellerID);
        statement.executeUpdate();
        
        conn.commit();
        conn.setAutoCommit(true);
    }
    
    /**
     * Adds money to a specific citizen.
     *
     * @param player The player associated to the citizen
     * @param amount The amount of money to add (can be negative)
     */
    public static void addMoney(Player player, int amount) throws SQLException
    {
        PreparedStatement statement =
            conn.prepareStatement("UPDATE citizen SET balance = balance + ? WHERE player = ?");
        
        statement.setInt(1, amount);
        statement.setString(2, player.getUniqueId().toString());
        statement.executeUpdate();
    }
    
    /**
     * Marks a citizen as dead, so that no player is linked to it anymore.
     *
     * @param player The player associated to the citizen
     */
    public static void killCitizen(Player player) throws SQLException
    {
        PreparedStatement statement = conn.prepareStatement("UPDATE citizen SET player = 'dead' WHERE player = ?");
        statement.setString(1, player.getUniqueId().toString());
        statement.executeUpdate();
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
