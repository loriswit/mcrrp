package me.olybri.mcrrp.util;// Created by Loris Witschard on 9/5/2017.

import org.bukkit.block.Block;
import org.bukkit.block.BlockFace;
import org.bukkit.entity.Player;
import org.bukkit.material.DirectionalContainer;
import org.bukkit.material.Door;
import org.bukkit.material.MaterialData;
import org.bukkit.material.Openable;

import java.sql.SQLException;

/**
 * Class representing a lockable block that behaves according to a specific player.
 */
public class Lockable
{
    private Block block;
    private Player player;
    
    /**
     * Creates a lockable block
     *
     * @param block  The lockable block
     * @param player The player interacting with the block
     * @return The new lockable, or <i>null</i> if the block is not lockable
     */
    public static Lockable create(Block block, Player player)
    {
        MaterialData data = block.getState().getData();
        
        if(!(data instanceof DirectionalContainer) && !(data instanceof Openable))
            return null;
        
        if(data instanceof Door && ((Door) data).isTopHalf())
            return new Lockable(block.getRelative(BlockFace.DOWN), player);
        else
            return new Lockable(block, player);
    }
    
    /**
     * Tells if the block is locked.
     *
     * @return <i>true</i> if the block is locked, <i>false</i> if not
     */
    public boolean locked() throws SQLException
    {
        return Database.locked(block);
    }
    
    /**
     * Tells if the player can interact with the block.
     *
     * @return <i>true</i> if the player is authorized to interact, <i>false</i> if not
     */
    public boolean authorized() throws SQLException
    {
        return Database.authorized(block, player);
    }
    
    /**
     * Locks the block.
     *
     * @param name The name of the lock
     */
    public void lock(String name) throws SQLException
    {
        Database.lock(player, block, name);
    }
    
    /**
     * Unlocks the block.
     */
    public void unlock() throws SQLException
    {
        Database.unlock(block);
    }
    
    private Lockable(Block block, Player player)
    {
        this.block = block;
        this.player = player;
    }
}
