<?php

/**
 * Page displaying a conversation between two citizen.
 */
class Conversation extends Page
{
    protected $userOnly = true;
    
    protected function title()
    {
        return ":@".$_GET["data"].":";
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
            $msgDay = strftime("%e", $date);
            if($day != $msgDay)
            {
                $day = $msgDay;
                $messageList .= "<p align='center'>*".strftime("%e %B %Y", $date)."*</p>";
            }
            
            $seen = "";
            
            if($message["sender_id"] == $this->citizen["id"])
            {
                $align = "right";
                if($message["seen"])
                {
                    $status = ":icon_seen:";
                    $seen = tr("read").": ".strftime("%e %B %Y, %H:%M", $message["seen"]);
                }
                else
                    $status = ":icon_sent:";
            }
            else
            {
                $align = "left";
                if($message["seen"])
                    $status = "";
                else
                    $status = "[".tr("new")."]";
            }
            
            $body = nl2br(htmlspecialchars($message["body"]));
            $body = str_replace("@", "&#64;", $body);
            
            $messageList .= "<p align='$align' title='$seen'>".$body
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
    }
}
