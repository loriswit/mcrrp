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
        $employment = [
            "Governments" => $this->db->governments(),
            "Banks" => $this->db->banks(),
            "Presses" => $this->db->presses(),
            "Other companies" => $this->db->otherCompanies()
        ];
        
        $this->set("employment", $employment);
    }
    
    protected function submit()
    {
    }
}

