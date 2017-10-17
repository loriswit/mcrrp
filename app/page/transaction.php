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
        
        $transactions = $this->db->transactions($this->citizen["id"], false, $this->sortBy);
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
            $transaction["bought"] = !$transaction["buyer_state"] && $transaction["buyer_id"] == $this->citizen["id"];
            $transaction["buyer"] = $buyer;
            $transaction["seller"] = $seller;
        }
        
        $this->set("states", $states);
        $this->set("codes", $codes);
        $this->set("transactions", $transactions);
        
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
        
        if(empty($_POST["description"]))
            throw new InvalidInputException("Please provide a description.");
        
        $this->db->addTransaction(
            $this->citizen["id"], false, $receiver["id"], $sellerState, $_POST["amount"], $_POST["description"]);
        
        $currency = $this->db->state($this->citizen["state_id"])["currency"];
        $this->set("info", tr("You paid")." ".$currency." ".$_POST["amount"]." ".tr("to")." ".$sellerName.".");
        
        // reload citizen
        $this->citizen = $this->db->citizen($this->citizen["id"]);
    }
}
