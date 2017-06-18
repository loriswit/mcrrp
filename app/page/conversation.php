<?php

class Conversation extends Page
{
    protected $userOnly = true;
    
    protected function title()
    {
        return ":".$_GET["data"].":";
    }
    
    protected function run()
    {
        $code = $_GET["data"];
        $contact = $this->db->citizenByCode($code);
        
        if(empty($code) || empty($contact) || $this->citizen["id"] == $contact["id"])
            header("Location: /message");
        
        $day = 0;
        $message_list = "";
        foreach($this->db->messages($this->citizen["id"], $contact["id"]) as $message)
        {
            $date = $message["timestamp"];
            $msg_day = $date - $date % 86400;
            if($day < $msg_day)
            {
                $day = $msg_day;
                $message_list .= "<p><b>".strftime("%e %B %Y", $day)."</b></p>";
            }
            
            if($message["sender_id"] == $this->citizen["id"])
                $name = tr("You");
            else
                $name = ":$code:";
            
            $message_list .= "<p>".strftime("%H:%M", $date)." <b>$name</b><br>\n".$message["body"]."</p>\n";
        }
        
        $this->tpl->set("code", $code);
        $this->tpl->set("messages", $message_list);
    }
    
    protected function submit()
    {
        $receiver = $this->db->citizenByCode($_GET["data"]);
        $this->db->addMessage($this->citizen["id"], $receiver["id"], $_POST["body"]);
        
        $this->messageCount = $this->db->messageCount($this->citizen["id"]);
    }
}
