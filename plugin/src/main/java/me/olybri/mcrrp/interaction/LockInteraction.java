package me.olybri.mcrrp.interaction;// Created by Loris Witschard on 9/6/2017.

import me.olybri.mcrrp.util.ItemName;
import me.olybri.mcrrp.util.Lockable;
import me.olybri.mcrrp.util.Message;
import org.bukkit.block.Block;
import org.bukkit.block.BlockFace;
import org.bukkit.entity.Player;
import org.bukkit.inventory.ItemStack;

import static me.olybri.mcrrp.util.Translation.tr;

/**
 * Class representing an interaction with a lockable block in order to lock it.
 */
public class LockInteraction extends BlockInteraction
{
    private String name;
    
    /**
     * Constructs the interaction.
     *
     * @param name The name of the lock
     */
    public LockInteraction(String name)
    {
        this.name = name;
    }
    
    @Override
    protected boolean run(Player player, Block block, BlockFace face) throws Exception
    {
        Lockable lockable = Lockable.create(block, player);
        
        if(lockable == null)
            return false;
        
        if(lockable.locked())
        {
            new Message(tr("This block is already locked") + ".").send(player);
            return true;
        }
        
        lockable.lock(name);
        new Message(tr(new ItemName(new ItemStack(block.getType())) + " locked") + ".").send(player);
        
        return true;
    }
}
