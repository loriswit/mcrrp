<?php

/**
 * Page displaying informations about a specific company.
 */
class Company extends Page
{
    protected $userOnly = true;
    
    private $company;
    
    protected function title()
    {
        return $this->company["name"];
    }
    
    protected function run()
    {
        $id = $_GET["data"];
        $this->company = $this->db->company($id);
        
        $founder = $this->db->citizen($this->company["founder_id"])["code"];
        $leaders = array_column($this->db->leaders($id), "code");
        if(empty($leaders))
            $leaders = "none";
        
        $this->tpl->set("description", $this->company["description"]);
        $this->tpl->set("date", strftime("%e %B %Y", $this->company["founded"]));
        $this->tpl->set("founder", $founder);
        $this->tpl->set("codes", $leaders);
        $this->tpl->set("presentation", $this->company["presentation"]);
    }
    
    protected function submit()
    {
    }
}

