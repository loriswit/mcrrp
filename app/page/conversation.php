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
                $message_list .= "<p align='center'><b>".strftime("%e %B %Y", $day)."</b></p>";
            }
            
            if($message["sender_id"] == $this->citizen["id"])
            {
                $align = "right";
                if($message["seen"])
                    $status = "&#10003";
                else
                    $status = "&#11208";
            }
            else
            {
                $align = "left";
                if($message["seen"])
                    $status = "";
                else
                    $status = "[".tr("new")."]";
            }
            
            $seen = tr("read").": ".strftime("%e %B %Y, %H:%M", $message["seen"]);
            
            $message_list .= "<p align='$align' title='$seen'>".$message["body"]
                ."<br>\n$status [".strftime("%H:%M", $date)."]</p>\n";
        }
        
        // mark messages as read
        $this->db->readMessages($contact["id"], $this->citizen["id"]);
        
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
