<?php

$title = "Transactions";

$info = "";
if(isset($_POST["pay"]))
{
    // TODO prevent negative balance
    
    $code = strtoupper($_POST["code"]);
    if($code == $citizen["code"])
        $info = tr("You cannot pay to yourself").".";
    else
    {
        $receiver = $db->citizen_by_code($code);
        if(empty($receiver))
            $info = tr("Invalid receiver's code").".";
        
        else
        {
            $db->add_transaction($citizen["id"], $receiver["id"], $_POST["amount"], $_POST["description"]);
            $info = tr("You paid")." ".$_POST["amount"]." ".tr("to")." ".$receiver["first_name"]." ".$receiver["last_name"].".";
            
            // reload citizen
            $citizen = $db->citizen($citizen["id"]);
        }
    }
}

$tpl = new Template("transaction");
$tpl->set("info", $info);

if(isset($_GET["sortby"]))
    $sort_by = $_GET["sortby"];
else
    $sort_by = "timestamp";

$columns = [
    "timestamp" => tr("Date"),
    "buyer_id" => tr("Buyer"),
    "seller_id" => tr("Seller"),
    "amount" => tr("Amount"),
    "description" => tr("Description")
];

$header = "<tr>\n";
foreach($columns as $key => $value)
    $header .= "<th>".($sort_by == $key ? $value : "<a href='?sortby=$key'>$value</a>")."</th>\n";
$header .= "</tr>\n";

$tpl->set("header", $header);

if($db->transaction_count($citizen["id"]) == 0)
    $tpl->set("transactions", "<tr><td colspan='5'>No transactions.</td></tr>");
else
{
    $transact_list = "";
    foreach($db->transactions($citizen["id"], $sort_by) as $transaction)
    {
        $buyer = $db->citizen($transaction["buyer_id"]);
        $receiver = $db->citizen($transaction["seller_id"]);
        
        if($buyer["id"] == $citizen["id"])
        {
            $buyer_name = "<b>[@You]</b>";
            $seller_name = $receiver["first_name"]." ".$receiver["last_name"]." (".$receiver["code"].")";
            $sign = "-";
        }
        else
        {
            $buyer_name = $buyer["first_name"]." ".$buyer["last_name"]." (".$buyer["code"].")";
            $seller_name = "<b>[@You]</b>";
            $sign = "+";
        }
        
        $date = strftime("%A %e %B %Y, %H:%M", $transaction["timestamp"]);
        
        $transact_list .= "<tr>\n"
            ."<td>$date</td>\n"
            ."<td>$buyer_name</td>\n"
            ."<td>$seller_name</td>\n"
            ."<td>$sign ".$transaction["amount"]."</td>\n"
            ."<td>".$transaction["description"]."</td>\n"
            ."</tr>\n";
    }
    
    $tpl->set("transactions", $transact_list);
}
