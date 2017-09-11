<?php

/**
 * Page displaying all conversations
 */
class Message extends Page
{
    protected $userOnly = true;
    
    protected function title()
    {
        return "Messages";
    }
    
    protected function run()
    {
        $codes = $this->db->knownCodes($this->citizen["id"]);
        
        $dates = array();
        $names = array();
        $messages = array();
        
        foreach($this->db->conversations($this->citizen["id"]) as $conversation)
        {
            $dates[] = strftime("%e %B %Y, %H:%M", $conversation["timestamp"]);
            
            if($conversation["sender_id"] == $this->citizen["id"])
            {
                $contactID = $conversation["receiver_id"];
                if($conversation["seen"])
                    $message = ":icon_seen:";
                else
                    $message = ":icon_sent:";
            }
            else
            {
                $contactID = $conversation["sender_id"];
                if($conversation["seen"])
                    $message = "";
                else
                    $message = "[".tr("new")."]";
            }
            
            $contact = $this->db->citizen($contactID);
            $name = ":".$contact["code"].":";
            
            $unreadMessages = $this->db->unreadMessageCountFrom($contactID, $this->citizen["id"]);
            if($unreadMessages > 0)
                $name .= " ($unreadMessages)";
            
            $message .= " ".htmlspecialchars($conversation["body"]);
            $message = str_replace("@", "&#64;", $message);
            
            $names[] = $name;
            $messages[] = $message;
        }
        
        $this->tpl->set("codes", $codes);
        $this->tpl->set("dates", $dates);
        $this->tpl->set("names", $names);
        $this->tpl->set("messages", $messages);
    }
    
    protected function submit()
    {
        $code = strtoupper($_POST["code"]);
        
        if($code == $this->citizen["code"])
            throw new InvalidInputException("You cannot start a conversation with yourself.");
        
        if(empty($this->db->citizenByCode($code)))
            throw new InvalidInputException("Invalid contact's code.");
        
        header("Location: /conversation/$code");
    }
}
