package me.olybri.mcrrp.interaction;// Created by Loris Witschard on 7/4/2017.

import me.olybri.mcrrp.MCRRP;
import org.bukkit.block.Block;
import org.bukkit.block.BlockFace;
import org.bukkit.entity.Player;
import org.bukkit.event.player.PlayerEvent;
import org.bukkit.event.player.PlayerInteractEvent;

public abstract class BlockInteraction implements Interaction
{
    @Override
    public final boolean apply(PlayerEvent event)
    {
        if(!(event instanceof PlayerInteractEvent))
            return false;
    
        Block block = ((PlayerInteractEvent) event).getClickedBlock();
        BlockFace blockFace = ((PlayerInteractEvent) event).getBlockFace();
        
        try
        {
            boolean success = run(event.getPlayer(), block, blockFace);
            ((PlayerInteractEvent) event).setCancelled(success);
            return success;
        }
        catch(Exception e)
        {
            MCRRP.log().severe(e.getMessage());
            e.printStackTrace();
        }
    
        return false;
    }
    
    protected abstract boolean run(Player player, Block block, BlockFace face) throws Exception;
}
