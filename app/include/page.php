<?php

abstract class Page
{
    protected $citizen;
    
    protected $db;
    protected $tpl;
    
    protected $visitorOnly = false;
    protected $userOnly = false;
    
    abstract protected function title();
    
    abstract protected function run();
    
    abstract protected function submit();
    
    public function __construct()
    {
        $this->db = new Database();
        $this->tpl = new Template(get_class($this));
        
        if($this->userOnly && !LOGGED || $this->visitorOnly && LOGGED)
            header("Location: /");
        
        if($this->userOnly)
            $this->citizen = $this->db->citizenByUUID($_SESSION["uuid"]);
    }
    
    public function render()
    {
        if(isset($_POST["submit"]))
            try
            {
                $this->submit();
            }
            catch(InvalidInputException $exception)
            {
                $this->tpl->setError(tr($exception->getMessage()));
            }
        
        $this->run();
        $mainTpl = new Template("main");
        
        if(LOGGED)
        {
            $unreadMessages = $this->db->unreadMessageCount($this->citizen["id"]);
            $unreadTransactions = $this->db->unreadTransactionCount($this->citizen["id"], false);
    
            $state = $this->db->state($this->citizen["state_id"]);
            $headerTpl = new Template("user");
            $headerTpl->set("uuid", $this->citizen["player"]);
            $headerTpl->set("code", $this->citizen["code"]);
            $headerTpl->set("role", "n/a");
            $headerTpl->set("balance", $this->citizen["balance"]);
            $headerTpl->set("state", $state["name"]);
            $headerTpl->set("msg_count", $unreadMessages > 0 ? " ($unreadMessages)" : "");
            $headerTpl->set("transac_count", $unreadTransactions > 0 ? " ($unreadTransactions)" : "");
        }
        else
            $headerTpl = new Template("visitor");
        
        
        $headerTpl->set("title", $this->title());
        
        $mainTpl->set("lang", LANG);
        $mainTpl->set("title", $this->title());
        $mainTpl->set("header", $headerTpl->html());
        $mainTpl->set("content", $this->tpl->html());
        $mainTpl->set("en", LANG == "en" ? "selected" : "");
        $mainTpl->set("fr", LANG == "fr" ? "selected" : "");
        $html = $mainTpl->html();
        
        // replace :XXXX: codes by names
        preg_match_all("/:(@?[a-zA-Z\d]{4}):/", $html, $matches);
        foreach(array_unique($matches[1]) as $match)
        {
            $link = strlen($match) == 5;
            
            if($link)
                $code = substr($match, 1);
            else
                $code = $match;
            
            $otherCitizen = $this->db->citizenByCode(strtoupper($code));
            if(empty($otherCitizen))
                continue;
            
            $name = $otherCitizen["first_name"]." ".$otherCitizen["last_name"];
            if($link && $this->citizen["id"] != $otherCitizen["id"])
                $html = str_replace(":$match:", "<a href='/conversation/".$otherCitizen["code"]."'>$name</a>", $html);
            else
                $html = str_replace(":$match:", $name, $html);
        }
        
        return $html;
    }
}
