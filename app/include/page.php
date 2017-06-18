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
            $header_tpl->set("msg_count", $this->db->messageCount($this->citizen["id"]));
            $header_tpl->set("transac_count", $this->db->transactionCount($this->citizen["id"], false));
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
        preg_match_all("/:([a-zA-Z\d]{4}):/", $html, $matches);
        foreach(array_unique($matches[1]) as $match)
        {
            $citizen = $this->db->citizenByCode(strtoupper($match));
            if(empty($citizen))
                continue;
            
            $name = $citizen["first_name"]." ".$citizen["last_name"];
            if($this->citizen["id"] == $citizen["id"])
                $html = str_replace(":$match:", $name, $html);
            else
                $html = str_replace(":$match:", "<a href='/message?to=".$citizen["code"]."'>$name</a>", $html);
        }
        
        return $html;
    }
}
