<?php

class Join extends Page
{
    protected $visitorOnly = true;
    
    protected function title()
    {
        return "Register";
    }
    
    protected function run()
    {
        if(!isset($_SESSION["uuid"]))
            header("Location: /");
    
        $uuid = $_SESSION["uuid"];
    
        $states = "";
        foreach($this->db->states() as $state)
            $states .= "<option value=".$state["id"].">".$state["name"]."</option>";
    
        $this->tpl->set("uuid", $uuid);
        $this->tpl->set("name", $_SESSION["username"]);
        $this->tpl->set("states", $states);
    }
    
    protected function submit()
    {
        if(!isset($_SESSION["uuid"]))
            header("Location: /");
        
        $firstName = $_POST["first_name"];
        $lastName = $_POST["last_name"];
    
        if(!preg_match("/^[\\pL-’' ]*$/u", $lastName) || !preg_match("/^[\\pL-’' ]*$/u", $firstName))
            throw new InvalidInputException("Your name must only contain letters, spaces, dashes and apostrophes.");
    
        $sex = $_POST["sex"];
        $stateID = $_POST["state"];
        $balance = $this->db->state($stateID)["initial"];
    
        // format names
        $firstName = str_replace("'", "’", $firstName);
        $lastName = str_replace("'", "’", $lastName);
    
        $firstName = mb_convert_case($firstName, MB_CASE_TITLE);
        $lastName = mb_convert_case($lastName, MB_CASE_TITLE);
    
        // try 10 name based code ; if it fails, then try random codes
        for($i = 0; $i < 10; $i++)
        {
            $code = Code::generate($firstName.$lastName);
            if($this->db->codeAvailable($code))
                break;
            $code = "";
        }
        if(empty($code))
            do
            {
                $code = Code::random();
            }
            while(!$this->db->codeAvailable($code));
    
        // send to database
        $this->db->addCitizen($code, $firstName, $lastName, $sex, $stateID, $balance, $_SESSION["uuid"]);
        
        $_SESSION["logged"] = true;
        header("Location: /");
    }
}

