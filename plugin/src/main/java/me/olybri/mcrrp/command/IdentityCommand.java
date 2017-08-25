package me.olybri.mcrrp.command;// Created by Loris Witschard on 7/6/2017.

import me.olybri.mcrrp.util.Database;
import me.olybri.mcrrp.util.Message;
import org.bukkit.entity.Player;

import java.sql.ResultSet;
import java.util.List;

import static me.olybri.mcrrp.util.Translation.tr;

/**
 * Command that prints the player's identity.
 */
public class IdentityCommand extends PlayerCommand
{
    /**
     * Constructs the identity command.
     */
    public IdentityCommand()
    {
        super(0, true);
    }
    
    @Override
    protected boolean run(Player player, List<String> args) throws Exception
    {
        ResultSet citizen = Database.citizen(player);
        ResultSet state = Database.state(citizen.getInt("state_id"));
        Message msg = new Message("",
            tr("First name(s)") + ": {value:" + citizen.getString("first_name") + "}\n"
                + tr("Last name(s)") + ": {value:" + citizen.getString("last_name") + "}\n"
                + tr("Code") + ": {value:" + citizen.getString("code") + "}\n"
                + tr("Sex") + ": {value:" + citizen.getString("sex") + "}\n"
                + tr("State") + ": {value:" + state.getString("name") + "}");
        
        setMessage(msg);
        return true;
    }
}
