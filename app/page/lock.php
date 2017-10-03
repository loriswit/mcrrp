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
        
        foreach($locks as &$lock)
            $lock["authorized"] = $this->db->authorized($lock["id"]);
        
        $this->set("codes", $codes);
        $this->set("locks", $locks);
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
