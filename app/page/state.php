<?php

/**
 * Page displaying all transactions of the state
 */
class State extends Transaction
{
    protected $isState = true;
    
    protected function title()
    {
        return "State transactions";
    }
    
    protected function run()
    {
        parent::run();
        $this->set("readonly", !$this->db->isGovernor($this->citizen["id"], true));
    }
    
    protected function submit()
    {
        if($this->db->isGovernor($this->citizen["id"], true))
            parent::submit();
    }
}
