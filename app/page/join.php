<?php

$title = "Register";

$uuid = $_SESSION["uuid"];
$error = "";

if(isset($_POST["join"]))
{
    $first_name = $_POST["first_name"];
    if(!preg_match("/^[\\pL-’' ]*$/u", $first_name))
        $error = tr("First name")." ".tr("must only contain letters, spaces, dashes and apostrophes.");
    
    $last_name = $_POST["last_name"];
    if(!preg_match("/^[\\pL-’' ]*$/u", $last_name))
        $error = (empty($error) ? "" : $error."<br>")
            .tr("Last name")." ".tr("must only contain letters, spaces, dashes and apostrophes.");
    
    if(empty($error))
    {
        $sex = $_POST["sex"];
        $state = $_POST["state"];
        $balance = 200;
        
        // format names
        $first_name = str_replace("'", "’", $first_name);
        $last_name = str_replace("'", "’", $last_name);
        
        $first_name = mb_convert_case($first_name, MB_CASE_TITLE, "utf-8");
        $last_name = mb_convert_case($last_name, MB_CASE_TITLE, "utf-8");
        
        // code generation constants
        $code_chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $code_length = 4;
        
        // generates a random code
        $random_code = function() use ($code_chars, $code_length)
        {
            $code = "";
            while(strlen($code) != $code_length)
                $code .= $code_chars[mt_rand(0, strlen($code_chars) - 1)];
            
            return $code;
        };
        
        // generates a code according to the name
        $name_code = function($name) use ($code_chars, $code_length, $random_code)
        {
            $code = "";
            preg_match_all("/\\p{Lu}/u", $name, $matches);
            foreach($matches[0] as $letter)
                if(strpos($code_chars, $letter) !== FALSE)
                    $code .= $letter;
            
            if(strlen($code) == 0)
                return $random_code();
            
            if(strlen($code) > $code_length - 1)
                $code = substr($code, 0, $code_length - 1);
            
            while(strlen($code) != $code_length)
                $code .= mt_rand(0, 2);
            
            return $code;
        };
        
        // try 10 name based code ; if it fails, then try random codes
        for($i = 0; $i < 1; $i++)
        {
            $code = $name_code($first_name.$last_name);
            if($db->code_available($code))
                break;
            $code = "";
        }
        if(empty($code))
            do
            {
                $code = $random_code();
            }
            while(!$db->code_available($code));
    
        // send to database
        $db->add_citizen($code, $first_name, $last_name, $sex, $state, $balance, $uuid);
        header("Location: /");
    }
}

//unset($_SESSION["uuid"]);

$body_tpl = new Template("join");
$body_tpl->set("uuid", $uuid);
$body_tpl->set("name", $_SESSION["username"]);
$body_tpl->set("error", $error);
