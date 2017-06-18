<?php

use Symfony\Component\Yaml\Yaml;

class Database
{
    private $pdo;
    
    public function __construct($environment = "")
    {
        // get identifiers from phinx.yml
        
        $config = Yaml::parse(file_get_contents("phinx.yml"));
        
        if(empty($environment))
            $environment = $config["environments"]["default_database"];
        
        $dbInfo = $config["environments"][$environment];
        
        $host = $dbInfo["host"];
        $name = $dbInfo["name"];
        $user = $dbInfo["user"];
        $pass = $dbInfo["pass"];
        
        $this->pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8", $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function isRegistered($uuid)
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM citizen WHERE player = ?");
        $st->execute([$uuid]);
        return $st->fetchColumn() > 0;
    }
    
    public function citizen($id)
    {
        $st = $this->pdo->prepare("SELECT * FROM citizen WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    public function citizenByUUID($uuid)
    {
        $st = $this->pdo->prepare("SELECT * FROM citizen WHERE player = ?");
        $st->execute([$uuid]);
        return $st->fetch();
    }
    
    public function citizenByCode($code)
    {
        $st = $this->pdo->prepare("SELECT * FROM citizen WHERE code = ?");
        $st->execute([strtoupper($code)]);
        return $st->fetch();
    }
    
    public function addCitizen($code, $firstName, $lastName, $sex, $state, $balance, $player)
    {
        $st = $this->pdo->prepare(
            "INSERT INTO citizen (code, first_name, last_name, sex, state_id, balance, player) "
            ."VALUES (?, ?, ?, ?, ?, ?, ?)");
        $st->execute([$code, $firstName, $lastName, $sex, $state, $balance, $player]);
    }
    
    public function states()
    {
        return $this->pdo->query("SELECT * FROM state")->fetchAll();
    }
    
    public function state($id)
    {
        $st = $this->pdo->prepare("SELECT * FROM state WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    public function codeAvailable($code)
    {
        $codes = $this->pdo->query("SELECT code FROM citizen")->fetchAll(PDO::FETCH_COLUMN);
        return !in_array($code, $codes);
    }
    
    // MESSAGES
    
    public function contacts($id)
    {
        $st = $this->pdo->prepare(
            "SELECT msg.* FROM message msg "
            ."INNER JOIN ("
                ."SELECT LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id), "
                    ."MAX(timestamp) AS most_recent "
                ."FROM message "
                ."GROUP BY LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)) group_msg "
            ."ON msg.timestamp = group_msg.most_recent "
            ."WHERE msg.sender_id = :id OR msg.receiver_id = :id");
        
        $st->execute([":id" => $id]);
        return $st->fetchAll();
    }
    
    public function messageCount($id)
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM message "
            ."WHERE sender_id = :id OR receiver_id = :id");
        $st->execute([":id" => $id]);
        return $st->fetchColumn();
    }
    
    // TRANSACTIONS
    
    public function transactions($id, $isState, $sortBy = "timestamp")
    {
        // check for valid sorting
        $columns = array_column($this->pdo->query("DESCRIBE transaction")->fetchAll(), "Field");
        if(!in_array($sortBy, $columns))
            header("Location: ".parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
        
        $isState = intval($isState);
        
        $st = $this->pdo->prepare(
            "SELECT * FROM transaction "
            ."WHERE (buyer_id = :id AND buyer_state = $isState) "
            ."OR (seller_id = :id AND seller_state = $isState) "
            ."ORDER BY $sortBy DESC");
        $st->execute([":id" => $id]);
        return $st->fetchAll();
    }
    
    public function transactionCount($playerID, $isState)
    {
        $isState = intval($isState);
        
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM transaction "
            ."WHERE (buyer_id = :id AND buyer_state = $isState) "
            ."OR (seller_id = :id AND seller_state = $isState)");
        $st->execute([":id" => $playerID]);
        return $st->fetchColumn();
    }
    
    public function addTransaction($buyerID, $buyerState, $sellerID, $sellerState, $amount, $description)
    {
        if($buyerState)
        {
            $buyerBalance = $this->state($buyerID)["balance"] - $amount;
            $buyerTable = "state";
        }
        else
        {
            $buyerBalance = $this->citizen($buyerID)["balance"] - $amount;
            $buyerTable = "citizen";
        }
        
        if($sellerState)
        {
            $sellerBalance = $this->state($sellerID)["balance"] + $amount;
            $sellerTable = "state";
        }
        else
        {
            $sellerBalance = $this->citizen($sellerID)["balance"] + $amount;
            $sellerTable = "citizen";
        }
        
        $this->pdo->beginTransaction();
        $st = $this->pdo->prepare(
            "INSERT INTO transaction (buyer_id, buyer_state, seller_id, seller_state, amount, description, timestamp) "
            ."VALUES (?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP(NOW()))");
        $st->execute([$buyerID, $buyerState, $sellerID, $sellerState, $amount, $description]);
        
        $st = $this->pdo->prepare("UPDATE $buyerTable SET balance = ? WHERE id = ?");
        $st->execute([$buyerBalance, $buyerID]);
        
        $st = $this->pdo->prepare("UPDATE $sellerTable SET balance = ? WHERE id = ?");
        $st->execute([$sellerBalance, $sellerID]);
        
        $this->pdo->commit();
    }
}
