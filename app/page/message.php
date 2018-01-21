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
        
        $conversations = $this->db->conversations($this->citizen["id"]);
        foreach($conversations as &$conversation)
        {
            $sent = $conversation["sender_id"] == $this->citizen["id"];
            if($sent)
                $contactID = $conversation["receiver_id"];
            else
                $contactID = $conversation["sender_id"];
            
            $conversation["body"] = preg_replace("/!\[.*\]\(.+\)/", "*Image*", $conversation["body"]);
            $conversation["date"] = new Date($conversation["timestamp"]);
            $conversation["sent"] = $sent;
            $conversation["contact"] = $this->db->citizen($contactID);
            $conversation["unread"] = $this->db->unreadMessageCountFrom($contactID, $this->citizen["id"]);
        }
        
        $this->set("codes", $codes);
        $this->set("conversations", $conversations);
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
