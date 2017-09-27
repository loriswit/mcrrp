<?php

/**
 * Page displaying all companies.
 */
class Employment extends Page
{
    protected $userOnly = true;
    
    protected function title()
    {
        return "Employment";
    }
    
    protected function run()
    {
        $governments = $this->db->governments();
        $banks = $this->db->banks();
        $presses = $this->db->presses();
        $others = $this->db->otherCompanies();
        
        $this->tpl->set("gov_ids", array_column($governments, "id"));
        $this->tpl->set("gov_names", array_column($governments, "name"));
        $this->tpl->set("gov_descriptions", array_column($governments, "description"));
    
        $this->tpl->set("bank_ids", array_column($banks, "id"));
        $this->tpl->set("bank_names", array_column($banks, "name"));
        $this->tpl->set("bank_descriptions", array_column($banks, "description"));
        
        $this->tpl->set("press_ids", array_column($presses, "id"));
        $this->tpl->set("press_names", array_column($presses, "name"));
        $this->tpl->set("press_descriptions", array_column($presses, "description"));
        
        $this->tpl->set("ids", array_column($others, "id"));
        $this->tpl->set("names", array_column($others, "name"));
        $this->tpl->set("descriptions", array_column($others, "description"));
    }
    
    protected function submit()
    {
    }
}

