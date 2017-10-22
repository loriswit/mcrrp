<?php

/**
 * Page displaying a registration form
 */
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
        
        $states = $this->db->states();
        
        $this->set("uuid", $uuid);
        $this->set("name", $_SESSION["username"]);
        $this->set("states", $states);
    }
    
    protected function submit()
    {
        if(!isset($_SESSION["uuid"]))
            header("Location: /");
        
        $firstName = trim($_POST["first_name"]);
        $lastName = trim($_POST["last_name"]);
        
        $nameRegex = "/^[\\pL-’' ]+$/u";
        if(!preg_match($nameRegex, $lastName) || !preg_match($nameRegex, $firstName))
            throw new InvalidInputException("Your name must only contain letters, spaces, dashes and apostrophes.");
        
        $sex = "N/A";
        $stateID = $_POST["state"];
        
        // format names
        $firstName = str_replace("'", "’", $firstName);
        $lastName = str_replace("'", "’", $lastName);
        
        $firstName = preg_replace("/\s+/", " ", $firstName);
        $lastName = preg_replace("/\s+/", " ", $lastName);
        
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
        $citizenID = $this->db->addCitizen($code, $firstName, $lastName, $sex, $stateID, $_SESSION["uuid"]);
        $state = $this->db->state($stateID);
        $this->db->addTransaction($stateID, true, $citizenID, false, $state["initial"],
            "Welcome gift from state ".$state["name"].".");
        
        $_SESSION["logged"] = true;
        header("Location: /");
    }
}

