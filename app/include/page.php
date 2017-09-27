<?php

use Symfony\Component\Yaml\Yaml;

/**
 * Abstract class representing a web page.
 */
abstract class Page
{
    /**
     * @var array Array containing all fields of the citizen linked to the
     * current user (can be undefined if no user is not logged in)
     */
    protected $citizen;
    
    /** @var Database Object representing the MCRRP Database */
    protected $db;
    
    /** @var Template Object representing the page's HTML template */
    protected $tpl;
    
    /** @var bool TRUE if the page is for visitor only, FALSE if not */
    protected $visitorOnly = false;
    
    /** @var bool TRUE if the page is for user only, FALSE if not */
    protected $userOnly = false;
    
    /**
     * Returns the title of the current page.
     *
     * @return string The title of the page
     */
    abstract protected function title();
    
    /**
     * Executes the current page and fills the HTML template.
     */
    abstract protected function run();
    
    /**
     * Submits POST data to the page
     */
    abstract protected function submit();
    
    /**
     * Creates a web page instance.
     */
    public function __construct()
    {
        $this->db = new Database();
        $this->tpl = new Template(get_class($this));
        
        if($this->userOnly && !LOGGED || $this->visitorOnly && LOGGED)
            header("Location: /");
        
        if(LOGGED)
        {
            $this->citizen = $this->db->citizenByUUID($_SESSION["uuid"]);
            if(empty($this->citizen))
            {
                session_unset();
                header("Location: /");
            }
        }
    }
    
    /**
     * Runs the page's script and generates HTML code.
     *
     * @return string The HTML code of the page
     */
    public function render()
    {
        if(isset($_POST["submit"]))
            try
            {
                $this->submit();
            }
            catch(InvalidInputException $exception)
            {
                $this->tpl->setError(tr($exception->getMessage()));
            }
        
        $this->run();
        $mainTpl = new Template("main");
        
        if(LOGGED)
        {
            $unreadMessages = $this->db->unreadMessageCount($this->citizen["id"]);
            $unreadTransactions = $this->db->unreadTransactionCount($this->citizen["id"], false);
            
            $state = $this->db->state($this->citizen["state_id"]);
            $headerTpl = new Template("user");
            $headerTpl->set("uuid", $this->citizen["player"]);
            $headerTpl->set("code", $this->citizen["code"]);
            $headerTpl->set("role", "n/a");
            $headerTpl->set("currency", $state["currency"]);
            $headerTpl->set("balance", $this->citizen["balance"]);
            $headerTpl->set("state", $state["name"]);
            $headerTpl->set("msg_count", $unreadMessages > 0 ? " ($unreadMessages)" : "");
            $headerTpl->set("transac_count", $unreadTransactions > 0 ? " ($unreadTransactions)" : "");
        }
        else
            $headerTpl = new Template("visitor");
        
        
        $headerTpl->set("title", $this->title());
        
        $mainTpl->set("lang", LANG);
        $mainTpl->set("title", $this->title());
        $mainTpl->set("header", $headerTpl->html());
        $mainTpl->set("content", $this->tpl->html());
        $mainTpl->set("en", LANG == "en" ? "selected" : "");
        $mainTpl->set("fr", LANG == "fr" ? "selected" : "");
        
        return $this->format($mainTpl->html());
    }
    
    /**
     * Formats the given text.
     *
     * @param string $str The input text
     * @return string The formatted code
     */
    private function format($str)
    {
        // replace :XXXX: codes by names
        preg_match_all("/:(@?[a-zA-Z\d]{4}):/", $str, $matches);
        foreach(array_unique($matches[1]) as $match)
        {
            $link = strlen($match) == 4;
            
            if($link)
                $code = $match;
            else
                $code = substr($match, 1);
            
            $otherCitizen = $this->db->citizenByCode(strtoupper($code));
            if(empty($otherCitizen))
                continue;
            
            $name = $otherCitizen["first_name"]." ".$otherCitizen["last_name"];
            if($link && $this->citizen["id"] != $otherCitizen["id"])
                $str = str_replace(":$match:", "<a href='/conversation/".$otherCitizen["code"]."'>$name</a>", $str);
            else
                $str = str_replace(":$match:", $name, $str);
        }
        
        // replace :MATERIAL.DAMAGE: by item name
        preg_match_all("/:([a-zA-Z_]+\.?\d*):/", $str, $matches);
        if(count($matches) != 0)
        {
            $itemNames = Yaml::parse(file_get_contents("../data/item/names.yml"));
            
            foreach(array_unique($matches[1]) as $match)
            {
                $args = explode(".", $match);
                $material = strtoupper($args[0]);
                $damage = (count($args) == 2 ? $args[1] : 0);
                
                if(isset($itemNames[$material][$damage]))
                    $str = str_replace(":$match:", $itemNames[$material][$damage], $str);
            }
        }
        
        $str = str_replace(":icon_seen:", "&#10003", $str);
        $str = str_replace(":icon_sent:", "&#11208", $str);
        
        $str = preg_replace("/(>.*)_([^_\n]*)_(.*<)/", "$1<i>$2</i>$3", $str);
        $str = preg_replace("/(>.*)\*([^\*\n]*)\*(.*<)/", "$1<b>$2</b>$3", $str);
        $str = preg_replace("/(>.*)~([^~\n]*)~(.*<)/", "$1<del>$2</del>$3", $str);
        
        
        return $str;
    }
}
