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
        $states = "";
        foreach($this->db->states() as $state)
            $states .= "<option value=".$state["id"].">".$state["name"]."</option>";
        
        $codes = "";
        foreach($this->db->knownCodes($this->citizen["id"]) as $code)
            $codes .= "<option value='$code'>:$code:</options>";
        
        if(isset($_GET["sortby"]))
            $this->sortBy = $_GET["sortby"];
        else
            $this->sortBy = "timestamp";
        
        $columns = [
            "timestamp" => tr("Date"),
            "buyer_id" => tr("Buyer"),
            "seller_id" => tr("Seller"),
            "amount" => tr("Amount"),
            "description" => tr("Description")
        ];
        
        $transactionCount = $this->db->transactionCount($this->citizen["id"], false);
        if($transactionCount > 0)
        {
            $header = "<tr>\n";
            foreach($columns as $key => $value)
                $header .= "<th>".($this->sortBy == $key ? $value : "<a href='?sortby=$key'>$value</a>")."</th>\n";
            $header .= "</tr>\n";
        }
        else
            $header = "";
        
        $this->tpl->setOptional("info");
        $this->tpl->set("states", $states);
        $this->tpl->set("codes", $codes);
        $this->tpl->set("header", $header);
        if($transactionCount == 0)
            $this->tpl->set("transactions", "<tr><td colspan=5>".tr("No transactions").".</td></tr>");
        else
            $this->tpl->set("transactions", $this->transactionList());
        
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
            $sellerName = ":@".$receiver["code"].":";
        }
        
        if(empty($receiver))
            throw new InvalidInputException("Invalid receiver's code.");
        
        if($_POST["amount"] < 1)
            throw new InvalidInputException("Invalid amount.");
        
        if($this->citizen["balance"] - $_POST["amount"] < 0)
            throw new InvalidInputException("Your balance is too low for this transaction.");
        
        $this->db->addTransaction($this->citizen["id"], false, $receiver["id"], $sellerState, $_POST["amount"], $_POST["description"]);
        $this->tpl->set("info", tr("You paid")." ".$_POST["amount"]." ".tr("to")." ".$sellerName.".");
        
        // reload citizen
        $this->citizen = $this->db->citizen($this->citizen["id"]);
    }
    
    private function transactionList()
    {
        $transactList = "";
        foreach($this->db->transactions($this->citizen["id"], false, $this->sortBy) as $transaction)
        {
            $sign = "";
            $status = "";
            $seen = "";
            
            if($transaction["buyer_state"])
            {
                $buyer = $this->db->state($transaction["buyer_id"]);
                $buyerName = tr("State").": ".$buyer["name"];
            }
            else
            {
                $buyer = $this->db->citizen($transaction["buyer_id"]);
                if($buyer["id"] == $this->citizen["id"])
                {
                    $buyerName = "<b>".tr("You")."</b>";
                    $sign = "-";
                    if($transaction["seen"])
                    {
                        $seen = tr("read").": ".strftime("%e %B %Y, %H:%M", $transaction["seen"]);
                        $status = "&#10003";
                    }
                    else
                        $status = "&#11208";
                }
                else
                    $buyerName = ":@".$buyer["code"].":";
            }
            
            if($transaction["seller_state"])
            {
                $receiver = $this->db->state($transaction["seller_id"]);
                $sellerName = tr("State").": ".$receiver["name"];
            }
            else
            {
                $receiver = $this->db->citizen($transaction["seller_id"]);
                if($receiver["id"] == $this->citizen["id"])
                {
                    $sellerName = "<b>".tr("You")."</b>";
                    $sign = "+";
                    if(!$transaction["seen"])
                        $status = "[".tr("new")."]";
                }
                else
                    $sellerName = ":@".$receiver["code"].":";
            }
            
            $date = strftime("%A %e %B %Y, %H:%M", $transaction["timestamp"]);
            
            $transactList .= "<tr>\n"
                ."<td>$date</td>\n"
                ."<td>$buyerName</td>\n"
                ."<td>$sellerName</td>\n"
                ."<td>$sign ".$transaction["amount"]."</td>\n"
                ."<td title='$seen'>$status ".$transaction["description"]."</td>\n"
                ."</tr>\n";
        }
        
        return $transactList;
    }
}
