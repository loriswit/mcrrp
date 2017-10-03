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
        $this->set("governments", $this->db->governments());
        $this->set("banks", $this->db->banks());
        $this->set("presses", $this->db->presses());
        $this->set("companies", $this->db->otherCompanies());
    }
    
    protected function submit()
    {
    }
}

