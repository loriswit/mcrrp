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
        $messageList = "";
        foreach($this->db->messages($this->citizen["id"], $contact["id"]) as $message)
        {
            $date = $message["timestamp"];
            $msgDay = $date - $date % 86400;
            if($day < $msgDay)
            {
                $day = $msgDay;
                $messageList .= "<p align='center'><b>".strftime("%e %B %Y", $day)."</b></p>";
            }
            
            $seen = "";
            
            if($message["sender_id"] == $this->citizen["id"])
            {
                $align = "right";
                if($message["seen"])
                {
                    $status = "&#10003";
                    $seen = tr("read").": ".strftime("%e %B %Y, %H:%M", $message["seen"]);
                }
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
            
            $messageList .= "<p align='$align' title='$seen'>".$message["body"]
                ."<br>\n$status [".strftime("%H:%M", $date)."]</p>\n";
        }
        
        // mark messages as read
        $this->db->readMessages($contact["id"], $this->citizen["id"]);
        
        $this->tpl->set("code", $code);
        $this->tpl->set("messages", $messageList);
    }
    
    protected function submit()
    {
        $receiver = $this->db->citizenByCode($_GET["data"]);
        $this->db->addMessage($this->citizen["id"], $receiver["id"], $_POST["body"]);
        
        $this->messageCount = $this->db->messageCount($this->citizen["id"]);
    }
}
