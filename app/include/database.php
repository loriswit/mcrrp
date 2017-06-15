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
        
        $db_info = $config["environments"][$environment];
        
        $host = $db_info["host"];
        $name = $db_info["name"];
        $user = $db_info["user"];
        $pass = $db_info["pass"];
        
        $this->pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8", $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function citizen($id)
    {
        $st = $this->pdo->prepare("SELECT * FROM citizen WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    public function citizen_by_uuid($uuid)
    {
        $st = $this->pdo->prepare("SELECT * FROM citizen WHERE player = ?");
        $st->execute([$uuid]);
        return $st->fetch();
    }
    
    public function citizen_by_code($code)
    {
        $st = $this->pdo->prepare("SELECT * FROM citizen WHERE code = ?");
        $st->execute([strtoupper($code)]);
        return $st->fetch();
    }
    
    public function add_citizen($code, $first_name, $last_name, $sex, $state, $balance, $player)
    {
        $st = $this->pdo->prepare(
            "INSERT INTO citizen (code, first_name, last_name, sex, state_id, balance, player) "
            ."VALUES (?, ?, ?, ?, ?, ?, ?)");
        $st->execute([$code, $first_name, $last_name, $sex, $state, $balance, $player]);
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
    
    public function code_available($code)
    {
        $codes = $this->pdo->query("SELECT code FROM citizen")->fetchAll(PDO::FETCH_COLUMN);
        return !in_array($code, $codes);
    }
    
    public function transactions($id, $is_state, $sort_by = "timestamp")
    {
        // check for valid sorting
        $columns = array_column($this->pdo->query("DESCRIBE transaction")->fetchAll(), "Field");
        if(!in_array($sort_by, $columns))
            header("Location: ".parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
        
        $is_state = intval($is_state);
        
        $st = $this->pdo->prepare(
            "SELECT * FROM transaction "
            ."WHERE (buyer_id = :id AND buyer_state = $is_state) "
            ."OR (seller_id = :id AND seller_state = $is_state) "
            ."ORDER BY $sort_by DESC");
        $st->execute([":id" => $id]);
        return $st->fetchAll();
    }
    
    public function transaction_count($player_id, $is_state)
    {
        $is_state = intval($is_state);
        
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM transaction "
            ."WHERE (buyer_id = :id AND buyer_state = $is_state) "
            ."OR (seller_id = :id AND seller_state = $is_state)");
        $st->execute([":id" => $player_id]);
        return $st->fetchColumn();
    }
    
    public function add_transaction($buyer_id, $buyer_state, $seller_id, $seller_state, $amount, $description)
    {
        if($buyer_state)
        {
            $buyer_balance = $this->state($buyer_id)["balance"] - $amount;
            $buyer_table = "state";
        }
        else
        {
            $buyer_balance = $this->citizen($buyer_id)["balance"] - $amount;
            $buyer_table = "citizen";
        }
        
        if($seller_state)
        {
            $seller_balance = $this->state($seller_id)["balance"] + $amount;
            $seller_table = "state";
        }
        else
        {
            $seller_balance = $this->citizen($seller_id)["balance"] + $amount;
            $seller_table = "citizen";
        }
        
        $this->pdo->beginTransaction();
        $st = $this->pdo->prepare(
            "INSERT INTO transaction (buyer_id, buyer_state, seller_id, seller_state, amount, description, timestamp) "
            ."VALUES (?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP(NOW()))");
        $st->execute([$buyer_id, $buyer_state, $seller_id, $seller_state, $amount, $description]);
        
        $st = $this->pdo->prepare("UPDATE $buyer_table SET balance = ? WHERE id = ?");
        $st->execute([$buyer_balance, $buyer_id]);
        
        $st = $this->pdo->prepare("UPDATE $seller_table SET balance = ? WHERE id = ?");
        $st->execute([$seller_balance, $seller_id]);
        
        $this->pdo->commit();
    }
}
