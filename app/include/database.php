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
    
    public function citizen($uuid)
    {
        $st = $this->pdo->prepare("SELECT * FROM citizen WHERE player = ?");
        $st->execute([$uuid]);
        return $st->fetch();
    }
    
    public function add_citizen($code, $first_name, $last_name, $sex, $state, $balance, $player)
    {
        $this->pdo->beginTransaction();
        $st = $this->pdo->prepare(
            "INSERT INTO citizen (code, first_name, last_name, sex, state, balance, player)"
            ."VALUES (?, ?, ?, ?, ?, ?, ?)");
        $st->execute([$code, $first_name, $last_name, $sex, $state, $balance, $player]);
        
        $this->pdo->commit();
    }
    
    public function code_available($code)
    {
        $codes = $this->pdo->query("SELECT code FROM citizen")->fetchAll(PDO::FETCH_COLUMN);
        return !in_array($code, $codes);
    }
}
