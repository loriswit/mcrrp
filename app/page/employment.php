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
            "governments" => $this->db->governments(),
            "banks" => $this->db->banks(),
            "presses" => $this->db->presses(),
            "other companies" => $this->db->otherCompanies(),
            "closed companies" => $this->db->closedCompanies()
        ];
        
        $jobs = $this->db->jobs($this->citizen["id"]);
        foreach($employment as &$companies)
            foreach($companies as &$company)
            {
                $index = array_search($company["id"], array_column($jobs, "company_id"));
                if($index !== false)
                {
                    $company["working"] = true;
                    $company["leading"] = $jobs[$index]["leader"];
                }
                else
                {
                    $company["working"] = false;
                    $company["leading"] = false;
                }
            }
        
        $this->set("employment", $employment);
    }
    
    protected function submit()
    {
        $this->db->addRequest($_POST["name"], $_POST["description"], $_POST["presentation"],
            $this->citizen["state_id"], $this->citizen["id"]);
        
        $this->set("info", "Request sent successfully.");
    }
}

