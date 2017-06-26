<?php

class NotFound extends Page
{
    protected function title()
    {
        return "Error";
    }
    
    protected function run()
    {
        http_response_code(404);
    }
    
    protected function submit()
    {
    }
}
