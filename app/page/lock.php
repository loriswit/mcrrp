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
        $codes = "";
        foreach($this->db->knownCodes($this->citizen["id"]) as $code)
            $codes .= "<option value='$code'>:$code:</options>";
        
        $locks = "";
        $authorized = "";
        foreach($this->db->locks($this->citizen["id"]) as $lock)
        {
            $lockID = $lock["id"];
            $name = $lock["name"];
            $type = $lock["type"];
            
            $locks .= "<option value=$lockID>$name</option>";
            $authorized .= "<tr>\n"
                ."<td>$type</td>\n"
                ."<td>$name</td>\n"
                ."<td>";
            
            foreach($this->db->authorized($lockID) as $citizenID)
            {
                $citizen = $this->db->citizen($citizenID);
                $authorized .= ":@".$citizen["code"].":; ";
            }
            
            $authorized .= "</td>\n</tr>\n";
        }
        
        $this->tpl->set("locks", $locks);
        $this->tpl->set("codes", $codes);
        $this->tpl->set("authorized", $authorized);
    }
    
    protected function submit()
    {
        $code = strtoupper($_POST["code"]);
        if($code == $this->citizen["code"])
            throw new InvalidInputException("You cannot authorize yourself.");
        
        $authorized = $this->db->citizenByCode($code);
        
        $this->db->addAuthorized($_POST["lock"], $authorized["id"]);
    }
}
