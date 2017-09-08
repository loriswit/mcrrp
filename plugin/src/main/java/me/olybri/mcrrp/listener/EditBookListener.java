package me.olybri.mcrrp.listener;// Created by Loris Witschard on 9/8/2017.

import me.olybri.mcrrp.MCRRP;
import me.olybri.mcrrp.util.Database;
import org.bukkit.entity.Player;
import org.bukkit.event.EventHandler;
import org.bukkit.event.Listener;
import org.bukkit.event.player.PlayerEditBookEvent;
import org.bukkit.inventory.meta.BookMeta;

import java.sql.ResultSet;
import java.sql.SQLException;

/**
 * Class listening to edit book events so that the book's author is the citizen and not the player.
 */
public class EditBookListener implements Listener
{
    @EventHandler
    public void onPlayerEditBookEvent(PlayerEditBookEvent event)
    {
        if(event.isSigning())
        {
            Player player = event.getPlayer();
            try
            {
                ResultSet citizen = Database.citizen(player);
                
                BookMeta book = event.getNewBookMeta();
                book.setAuthor(citizen.getString("first_name") + " " + citizen.getString("last_name"));
                event.setNewBookMeta(book);
            }
            catch(SQLException e)
            {
                MCRRP.error(e, player);
            }
        }
    }
}
