package me.olybri.mcrrp.interaction;// Created by Loris Witschard on 7/4/2017.

import org.bukkit.event.player.PlayerEvent;

/**
 * Interface representing a player interaction.
 */
public interface Interaction
{
    /**
     * Executes the interaction.
     *
     * @param event The associated event
     * @return <i>true</i> if the process succeeded, <i>false</i> if not
     */
    boolean apply(PlayerEvent event);
}
