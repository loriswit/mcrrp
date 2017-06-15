<?php

$title = "Transactions";

$info = "";
if(isset($_POST["pay"]))
{
    // TODO prevent negative balance
    
    $seller_state = ($_POST["receiver"] != "citizen");
    
    if($seller_state)
    {
        $receiver = $db->state($_POST["receiver"]);
        $seller_name = $receiver["name"];
    }
    else
    {
        $code = strtoupper($_POST["code"]);
        if($code == $citizen["code"])
            $info = tr("You cannot pay to yourself").".";
        else
        {
            $receiver = $db->citizen_by_code($code);
            $seller_name = $receiver["first_name"]." ".$receiver["last_name"];
        }
    }
    
    if(!isset($receiver) || empty($receiver))
        $info = tr("Invalid receiver's code").".";
    else
    {
        $db->add_transaction($citizen["id"], false, $receiver["id"], $seller_state, $_POST["amount"], $_POST["description"]);
        $info = tr("You paid")." ".$_POST["amount"]." ".tr("to")." ".$seller_name.".";
        
        // reload citizen
        $citizen = $db->citizen($citizen["id"]);
    }
}

$states = "";
foreach($db->states() as $state)
    $states .= "<option value=".$state["id"].">".$state["name"]."</option>";

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

if($db->transaction_count($citizen["id"], false) == 0)
    $transact_list = "<tr><td colspan=5>No transactions.</td></tr>";
else
{
    $transact_list = "";
    foreach($db->transactions($citizen["id"], false, $sort_by) as $transaction)
    {
        if($transaction["buyer_state"])
        {
            $buyer = $db->state($transaction["buyer_id"]);
            $buyer_name = tr("State").": ".$buyer["name"];
        }
        else
        {
            $buyer = $db->citizen($transaction["buyer_id"]);
            if($buyer["id"] == $citizen["id"])
            {
                $buyer_name = "<b>".tr("You")."</b>";
                $sign = "-";
            }
            else
                $buyer_name = $buyer["first_name"]." ".$buyer["last_name"]." (".$buyer["code"].")";
        }
        
        if($transaction["seller_state"])
        {
            $receiver = $db->state($transaction["seller_id"]);
            $seller_name = tr("State").": ".$receiver["name"];
        }
        else
        {
            $receiver = $db->citizen($transaction["seller_id"]);
            if($receiver["id"] == $citizen["id"])
            {
                $seller_name = "<b>".tr("You")."</b>";
                $sign = "+";
            }
            else
                $seller_name = $receiver["first_name"]." ".$receiver["last_name"]." (".$receiver["code"].")";
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
}

$tpl = new Template("transaction");
$tpl->set("info", $info);
$tpl->set("states", $states);
$tpl->set("header", $header);
$tpl->set("transactions", $transact_list);
