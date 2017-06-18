<?php

abstract class Page
{
    protected $citizen;
    protected $messageCount;
    protected $transactionCount;
    
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
        {
            $this->citizen = $this->db->citizenByUUID($_SESSION["uuid"]);
            $this->messageCount = $this->db->messageCount($this->citizen["id"]);
            $this->transactionCount = $this->db->transactionCount($this->citizen["id"], false);
        }
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
        $main_tpl = new Template("main");
        
        if(LOGGED)
        {
            $state = $this->db->state($this->citizen["state_id"]);
            $header_tpl = new Template("user");
            $header_tpl->set("uuid", $this->citizen["player"]);
            $header_tpl->set("code", $this->citizen["code"]);
            $header_tpl->set("role", "n/a");
            $header_tpl->set("balance", $this->citizen["balance"]);
            $header_tpl->set("state", $state["name"]);
            $header_tpl->set("msg_count", $this->messageCount);
            $header_tpl->set("transac_count", $this->transactionCount);
        }
        else
            $header_tpl = new Template("visitor");
        
        
        $header_tpl->set("title", $this->title());
        
        $main_tpl->set("lang", LANG);
        $main_tpl->set("title", $this->title());
        $main_tpl->set("header", $header_tpl->html());
        $main_tpl->set("content", $this->tpl->html());
        $main_tpl->set("en", LANG == "en" ? "selected" : "");
        $main_tpl->set("fr", LANG == "fr" ? "selected" : "");
        $html = $main_tpl->html();
        
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
