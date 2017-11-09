<?php

/**
 * Page displaying informations about a specific company.
 */
class Company extends Page
{
    protected $userOnly = true;
    protected $argsCount = 1;
    
    private $company;
    
    protected function title()
    {
        return $this->company["name"];
    }
    
    protected function run()
    {
        $id = $this->args[0];
        $this->company = $this->db->company($id);
        
        if(empty($id) || empty($this->company) || $this->company["request"] && !$this->citizen["governor"])
        {
            header("Location: /employment");
            exit;
        }
        
        $this->company["founder"] = $this->db->citizen($this->company["founder_id"]);
        $leaders = $this->db->leaders($id);
        
        $this->set("company", $this->company);
        $this->set("leaders", $leaders);
    }
    
    protected function submit()
    {
    }
}

