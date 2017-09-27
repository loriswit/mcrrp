<?php

/**
 * Page displaying all transactions
 */
class Transaction extends Page
{
    protected $userOnly = true;
    
    private $sortBy;
    
    protected function title()
    {
        return "Transactions";
    }
    
    protected function run()
    {
        if(isset($_GET["sortby"]))
            $this->sortBy = $_GET["sortby"];
        else
            $this->sortBy = "timestamp";
        
        $codes = $this->db->knownCodes($this->citizen["id"]);
        $states = $this->db->states();
        $dates = array();
        $buyers = array();
        $sellers = array();
        $amounts = array();
        $seen = array();
        $descriptions = array();
        
        foreach($this->db->transactions($this->citizen["id"], false, $this->sortBy) as $transaction)
        {
            $dates[] = strftime("%A %e %B %Y, %H:%M", $transaction["timestamp"]);
            
            $description = "";
            $amount = "";
            $read = "";
            
            if($transaction["buyer_state"])
            {
                $buyer = $this->db->state($transaction["buyer_id"]);
                $currency = $buyer["currency"];
                
                $buyers[] = tr("State").": ".$buyer["name"];
            }
            else
            {
                $buyer = $this->db->citizen($transaction["buyer_id"]);
                $currency = $this->db->state($buyer["state_id"])["currency"];
                
                if($buyer["id"] == $this->citizen["id"])
                {
                    $buyers[] = "*".tr("You")."*";
                    $amount = "- ".$currency." ".$transaction["amount"];
                    if($transaction["seen"])
                    {
                        $read = tr("read").": ".strftime("%e %B %Y, %H:%M", $transaction["seen"]);
                        $description = ":icon_seen:";
                    }
                    else
                        $description = ":icon_sent:";
                }
                else
                    $buyers[] = ":".$buyer["code"].":";
            }
            
            if($transaction["seller_state"])
            {
                $receiver = $this->db->state($transaction["seller_id"]);
                $sellers[] = tr("State").": ".$receiver["name"];
            }
            else
            {
                $receiver = $this->db->citizen($transaction["seller_id"]);
                if($receiver["id"] == $this->citizen["id"])
                {
                    $sellers[] = "*".tr("You")."*";
                    $amount = "+ ".$currency." ".$transaction["amount"];
                    if(!$transaction["seen"])
                        $description = "[".tr("new")."]";
                }
                else
                    $sellers[] = ":".$receiver["code"].":";
            }
            
            $descriptions[] = $description." ".$transaction["description"];
            $amounts[] = $amount;
            $seen[] = $read;
        }
        
        $this->tpl->setOptional("info");
        $this->tpl->set("state_ids", array_column($states, "id"));
        $this->tpl->set("state_names", array_column($states, "name"));
        $this->tpl->set("codes", $codes);
        $this->tpl->set("dates", $dates);
        $this->tpl->set("buyers", $buyers);
        $this->tpl->set("sellers", $sellers);
        $this->tpl->set("amounts", $amounts);
        $this->tpl->set("seen", $seen);
        $this->tpl->set("descriptions", $descriptions);
        
        // mark transactions as read
        $this->db->readTransactions($this->citizen["id"], false);
    }
    
    protected function submit()
    {
        $sellerState = ($_POST["receiver"] != "citizen");
        
        if($sellerState)
        {
            $receiver = $this->db->state($_POST["receiver"]);
            $sellerName = $receiver["name"];
        }
        else
        {
            $code = strtoupper($_POST["code"]);
            if($code == $this->citizen["code"])
                throw new InvalidInputException("You cannot pay to yourself.");
            
            $receiver = $this->db->citizenByCode($code);
            $sellerName = ":".$receiver["code"].":";
        }
        
        if(empty($receiver))
            throw new InvalidInputException("Invalid receiver's code.");
        
        if($_POST["amount"] < 1)
            throw new InvalidInputException("Invalid amount.");
        
        if($this->citizen["balance"] - $_POST["amount"] < 0)
            throw new InvalidInputException("Your balance is too low for this transaction.");
        
        $this->db->addTransaction(
            $this->citizen["id"], false, $receiver["id"], $sellerState, $_POST["amount"], $_POST["description"]);
        
        $currency = $this->db->state($this->citizen["state_id"])["currency"];
        $this->tpl->set("info", tr("You paid")." ".$currency." ".$_POST["amount"]." ".tr("to")." ".$sellerName.".");
        
        // reload citizen
        $this->citizen = $this->db->citizen($this->citizen["id"]);
    }
}
