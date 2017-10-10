<?php

/**
 * Page displaying a conversation between two citizen.
 */
class Conversation extends Page
{
    protected $userOnly = true;
    protected $argsCount = 1;
    
    protected function title()
    {
        return ":@".$this->args[0].":";
    }
    
    protected function run()
    {
        $code = $this->args[0];
        $contact = $this->db->citizenByCode($code);
        
        if(empty($code) || empty($contact) || $this->citizen["id"] == $contact["id"])
            header("Location: /message");
        
        $messages = $this->db->messages($this->citizen["id"], $contact["id"]);
        foreach($messages as &$message)
            $message["sent"] = $message["sender_id"] == $this->citizen["id"];
        
        // mark messages as read
        $this->db->readMessages($contact["id"], $this->citizen["id"]);
        
        $this->set("code", $code);
        $this->set("messages", $messages);
    }
    
    protected function submit()
    {
        $body = trim($_POST["body"]);
        if(empty($body))
            return;
        
        $receiver = $this->db->citizenByCode($this->args[0]);
        $this->db->addMessage($this->citizen["id"], $receiver["id"], trim($_POST["body"]));
    }
}
