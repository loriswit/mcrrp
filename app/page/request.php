<?php

/**
 * Page displaying all company requests.
 */
class Request extends Page
{
    protected $userOnly = true;
    
    protected function title()
    {
        return "Requests";
    }
    
    protected function run()
    {
        if(!$this->citizen["governor"])
        {
            header("Location: /");
            exit;
        }
        
        $requests = $this->db->requests($this->citizen["state_id"]);
        foreach($requests as &$request)
        {
            $request["founder"] = $this->db->citizen($request["founder_id"]);
            $request["date"] = new Date($request["founded"]);
        }
        
        $this->set("requests", $requests);
    }
    
    protected function submit()
    {
    }
}
