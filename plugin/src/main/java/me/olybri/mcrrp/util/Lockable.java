package me.olybri.mcrrp.util;// Created by Loris Witschard on 9/5/2017.

import me.olybri.mcrrp.MCRRP;
import org.bukkit.Location;
import org.bukkit.block.Block;
import org.bukkit.block.BlockFace;
import org.bukkit.entity.Player;
import org.bukkit.material.DirectionalContainer;
import org.bukkit.material.Door;
import org.bukkit.material.MaterialData;
import org.bukkit.material.Openable;

import java.sql.SQLException;

/**
 * Class representing a lockable block.
 */
public class Lockable
{
    private boolean locked;
    
    /**
     * Constructs a lockable block.
     *
     * @param locked <i>true</i> if the block is already locked, <i>false</i> if not
     */
    public Lockable(boolean locked)
    {
        this.locked = locked;
    }
    
    /**
     * Creates a lockable block that behaves according to a specific player.
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
        
        Location location;
        
        if(data instanceof Door && ((Door) data).isTopHalf())
            location = block.getRelative(BlockFace.DOWN).getLocation();
        else
            location = block.getLocation();
        
        try
        {
            boolean locked = !Database.authorized(player, location);
            return new Lockable(locked);
        }
        catch(SQLException e)
        {
            MCRRP.error(e, player);
        }
        
        return null;
    }
    
    /**
     * Tells if the block is locked.
     *
     * @return <i>true</i> if the block is locked, <i>false</i> if not
     */
    public boolean locked()
    {
        return locked;
    }
}
