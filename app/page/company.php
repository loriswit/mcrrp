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
        
        $this->citizen["leader"] = $this->db->isLeader($this->citizen["id"], $company["id"]);
        
        $this->set("company", $company);
        $this->set("citizen", $this->citizen);
    }
    
    protected function submit()
    {
        $id = $this->args[0];
        $company = $this->db->company($id);
        $action = $_POST["submit"];
        
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
                
                if($action == "close")
                    $this->db->closeCompany($company["id"]);
            }
        }
        
        if($this->db->isLeader($this->citizen["id"], $company["id"]) || $this->citizen["governor"])
            if($action == "edit")
                $this->db->updateCompanyInformations(
                    $company["id"], $_POST["name"], $_POST["description"],
                    mb_strtolower($_POST["profession"]), $_POST["presentation"]);
    }
}

