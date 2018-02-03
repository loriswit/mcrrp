<?php

/**
 * Page displaying all transactions
 */
class Transaction extends Page
{
    protected $userOnly = true;
    
    protected $isState = false;
    
    protected function title()
    {
        return "Transactions";
    }
    
    protected function run()
    {
        $codes = $this->db->knownCodes($this->citizen["id"]);
        $states = $this->db->states();
        $id = $this->isState ? $this->citizen["state_id"] : $this->citizen["id"];
        
        $transactions = $this->db->transactions($id, $this->isState);
        foreach($transactions as &$transaction)
        {
            if($transaction["buyer_state"])
                $buyer = $this->db->state($transaction["buyer_id"]);
            
            else
            {
                $buyer = $this->db->citizen($transaction["buyer_id"]);
                $buyer["currency"] = $this->db->state($buyer["state_id"])["currency"];
            }
            
            if($transaction["seller_state"])
                $seller = $this->db->state($transaction["seller_id"]);
            
            else
                $seller = $this->db->citizen($transaction["seller_id"]);
            
            $transaction["date"] = new Date($transaction["timestamp"]);
            $transaction["buyer"] = $buyer;
            $transaction["seller"] = $seller;
            $transaction["bought"] =
                $transaction["buyer_state"] == $this->isState
                && $transaction["buyer_id"] == $id;
        }
        
        $this->set("is_state", $this->isState);
        $this->set("states", $states);
        $this->set("codes", $codes);
        $this->set("transactions", $transactions);
        
        // mark transactions as read
        $this->db->readTransactions($id, $this->isState);
    }
    
    protected function submit()
    {
        $sellerState = ($_POST["receiver"] != "citizen");
        $state = $this->db->state($this->citizen["state_id"]);
        $buyer = $this->isState ? $state : $this->citizen;
        
        if($sellerState)
        {
            $seller = $this->db->state($_POST["receiver"]);
            if($this->isState && $seller["id"] == $buyer["id"])
                throw new InvalidInputException("You cannot pay to yourself.");
            
            $sellerName = $seller["name"];
        }
        else
        {
            $code = strtoupper($_POST["code"]);
            if(!$this->isState && $code == $buyer["code"])
                throw new InvalidInputException("You cannot pay to yourself.");
            
            $seller = $this->db->citizenByCode($code);
            $sellerName = ":".$seller["code"].":";
        }
        
        if(empty($seller))
            throw new InvalidInputException("Invalid receiver's code.");
        
        if($_POST["amount"] < 1)
            throw new InvalidInputException("Invalid amount.");
        
        if($buyer["balance"] - $_POST["amount"] < 0)
            throw new InvalidInputException("Your balance is too low for this transaction.");
        
        if(empty($_POST["description"]))
            throw new InvalidInputException("Please provide a description.");
        
        $this->db->addTransaction(
            $buyer["id"], $this->isState, $seller["id"], $sellerState, $_POST["amount"], $_POST["description"]);
        
        $this->set("info", tr("You paid")." ".$state["currency"]." ".$_POST["amount"]." ".tr("to")." ".$sellerName.".");
        
        // reload citizen
        $this->citizen = $this->db->citizen($this->citizen["id"]);
    }
}
