<?php

class Message extends Page
{
    protected $userOnly = true;
    
    protected function title()
    {
        return "Messages";
    }
    
    protected function run()
    {
        $contact_list = "";
        foreach($this->db->contacts($this->citizen["id"]) as $contact)
        {
            $date = strftime("%e %B %Y, %H:%M", $contact["timestamp"]);
            
            if($contact["sender_id"] == $this->citizen["id"])
            {
                $prefix = "&#11208;";
                $contactID = $contact["receiver_id"];
            }
            else
            {
                $prefix = "";
                $contactID = $contact["sender_id"];
            }
            
            $citizen = $this->db->citizen($contactID);
            
            $contact_list .= "<tr>\n"
                ."<td>$date</td>\n"
                ."<td>:".$citizen["code"].":</td>\n"
                ."<td>$prefix ".$contact["body"]."</td>\n"
                ."</tr>\n";
        }
        
        $this->tpl->set("contacts", $contact_list);
    }
    
    protected function submit()
    {
        // TODO: Implement submit() method.
    }
}
