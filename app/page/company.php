<?php

/**
 * Page displaying informations about a specific company.
 */
class Company extends Page
{
    protected $userOnly = true;
    protected $argsCount = 1;
    
    private $name;
    
    protected function title()
    {
        return $this->name;
    }
    
    protected function run()
    {
        $id = $this->args[0];
        $company = $this->db->company($id);
        $this->name = $company["name"];
        
        if(empty($id) || empty($company) || $company["request"] && !$this->citizen["governor"])
        {
            header("Location: /employment");
            exit;
        }
        
        $company["founder"] = $this->db->citizen($company["founder_id"]);
        $company["leaders"] = $this->db->leaders($company["id"]);
        $company["materials"] = $this->db->materials($company["id"]);
        
        $workers = $this->db->workers($company["id"]);
        foreach($workers as &$worker)
            $worker["citizen"] = $this->db->citizen($worker["citizen_id"]);
        
        $this->citizen["leader"] = $this->db->isLeader($this->citizen["id"], $company["id"]);
        
        $this->set("company", $company);
        $this->set("workers", $workers);
        $this->set("citizen", $this->citizen);
    }
    
    protected function submit()
    {
        $action = $_POST["submit"];
        
        $id = $this->args[0];
        $company = $this->db->company($id);
        
        // No actions are allowed if the company is closed
        if($company["closed"])
            return;
        
        $this->citizen["leader"] = $this->db->isLeader($this->citizen["id"], $company["id"]);
        $workers = $this->db->workers($company["id"]);
        
        if($this->citizen["governor"])
        {
            if($company["request"])
            {
                if($action == "accept" || $action == "reject")
                    $this->db->acceptRequest($company["id"], $action == "accept");
                
                if($action == "reject")
                    header("Location: /request");
            }
            else
            {
                if($action == "permission")
                {
                    $materials = array();
                    foreach(explode(PHP_EOL, $_POST["materials"]) as $itemName)
                    {
                        $material = Items::getMaterial($itemName);
                        if($material !== false)
                            $materials[] = $material;
                    }
                    
                    $this->db->updateCompanyPermissions(
                        $company["id"], isset($_POST["government"]), isset($_POST["bank"]),
                        isset($_POST["press"]), $materials);
                }
            }
        }
        
        if($this->citizen["leader"])
        {
            if($action == "promote")
            {
                $index = array_search($_POST["worker"], array_column($workers, "id"));
                if($index !== false)
                {
                    $isLeader = $workers[$index]["leader"];
                    $this->db->promote($_POST["worker"], !$isLeader);
                }
            }
            
            if($action == "hire")
            {
                $worker = $this->db->citizenByCode($_POST["code"]);
                if(!in_array($worker["id"], array_column($workers, "citizen_id")))
                    $this->db->hire($company["id"], $worker["id"]);
            }
            
            if($action == "dismiss")
                if(in_array($_POST["worker"], array_column($workers, "id")))
                    $this->db->dismiss($_POST["worker"]);
            
            if($action == "edit")
                $this->db->updateCompanyInformations(
                    $company["id"], $_POST["name"], $_POST["description"],
                    mb_strtolower($_POST["profession"]), $_POST["presentation"]);
        }
        
        if($this->citizen["leader"] || $this->citizen["governor"])
            if($action == "close" && !$company["closed"])
                $this->db->closeCompany($company["id"]);
    }
}

