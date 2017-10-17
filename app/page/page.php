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
    
    /** @var array Array containing all page arguments */
    protected $args;
    
    /** @var int Number of arguments needed to run the page */
    protected $argsCount = 0;
    
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
     *
     * @param array $args The page arguments
     */
    public function __construct($args)
    {
        $this->db = new Database();
        $twig = new Twig_Environment(new Twig_Loader_Filesystem("template"));
        $this->tpl = $twig->load("page/".strtolower(get_class($this)).".html");
        $this->args = $args;
        
        if(count($this->args) > $this->argsCount)
        {
            $page = strtolower(get_class($this));
            $this->args = array_slice($args, 0, $this->argsCount);
            array_unshift($this->args, $page);
            header("Location: /".implode("/", $this->args));
            exit;
        }
        
        if($this->userOnly && !LOGGED || $this->visitorOnly && LOGGED)
            header("Location: /");
        
        if(LOGGED)
        {
            $this->citizen = $this->db->citizenByUUID($_SESSION["uuid"]);
            if(empty($this->citizen))
                header("Location: /logout");
        }
    }
    
    /**
     * Defines a variable for the Twig template.
     *
     * @param string $key The variable name
     * @param mixed $value The value of the variable
     */
    protected function set($key, $value)
    {
        $this->variables[$key] = $value;
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
                $this->variables["error"] = tr($exception->getMessage());
            }
        
        $this->run();
        
        if(LOGGED)
        {
            $unreadMessages = $this->db->unreadMessageCount($this->citizen["id"]);
            $unreadTransactions = $this->db->unreadTransactionCount($this->citizen["id"], false);
            
            $state = $this->db->state($this->citizen["state_id"]);
            $this->variables["citizen"] = $this->citizen;
            $this->variables["role"] = "n/a";
            $this->variables["state"] = $state;
            $this->variables["msg_count"] = $unreadMessages;
            $this->variables["transac_count"] = $unreadTransactions;
            
            $this->variables["template"] = "user.html";
        }
        else
            $this->variables["template"] = "visitor.html";
        
        $this->variables["title"] = $this->title();
        $this->variables["langs"] = [
            ["id" => "en", "name" => "English"],
            ["id" => "fr", "name" => "Français"],
        ];
        
        return $this->format($this->tpl->render($this->variables));
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
                $str = str_replace(":$match:", "<a class='link' href='/conversation/".$otherCitizen["code"]."'>$name</a>", $str);
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
        
        $str = str_replace(":icon_seen:", "<i class='material-icons small-icon'>done</i>", $str);
        $str = str_replace(":icon_sent:", "<i class='material-icons small-icon'>play_arrow</i>", $str);
        $str = str_replace(":icon_forward:", "<i class='material-icons small-icon'>arrow_forward</i>", $str);
        $str = str_replace(":icon_back:", "<i class='material-icons small-icon'>arrow_back</i>", $str);
        $str = str_replace(":icon_mail:", "<i class='material-icons small-icon'>email</i>", $str);
        
        return $str;
    }
    
    private $tpl;
    private $variables = array();
}
