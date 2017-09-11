<?php

/**
 * Page displaying all locks.
 */
class Lock extends Page
{
    protected $userOnly = true;
    
    protected function title()
    {
        return "Locks";
    }
    
    protected function run()
    {
        $codes = $this->db->knownCodes($this->citizen["id"]);
        $locks = $this->db->locks($this->citizen["id"]);
        
        $authorized = array();
        foreach($locks as $lock)
        {
            $citizenList = "";
            foreach($this->db->authorized($lock["id"]) as $citizenID)
            {
                $citizen = $this->db->citizen($citizenID);
                $citizenList .= ":".$citizen["code"].":; ";
            }
            $authorized[] = rtrim($citizenList, "; ");
        }
        
        $this->tpl->set("lock_ids", array_column($locks, "id"));
        $this->tpl->set("codes", $codes);
        $this->tpl->set("lock_types", array_column($locks, "type"));
        $this->tpl->set("lock_names", array_column($locks, "name"));
        $this->tpl->set("authorized", $authorized);
    }
    
    protected function submit()
    {
        $code = strtoupper($_POST["code"]);
        if($code == $this->citizen["code"])
            return;
        
        $authorized = $this->db->citizenByCode($code);
        if(empty($authorized))
            throw new InvalidInputException("Invalid citizen's code.");
        
        if($_POST["submit"] == "add")
            $this->db->addAuthorized($_POST["lock"], $authorized["id"]);
        
        else if($_POST["submit"] == "remove")
            $this->db->removeAuthorized($_POST["lock"], $authorized["id"]);
    }
}
