<?php

/**
 * Class representing a connection to a MCRRP database.
 */
class Database
{
    private $pdo;
    
    /**
     * Creates a MCRRP database instance.
     */
    public function __construct()
    {
        $dbInfo = CONFIG["database"];
        
        $host = $dbInfo["host"];
        $name = $dbInfo["name"];
        $user = $dbInfo["user"];
        $pass = $dbInfo["pass"];
        
        $this->pdo = new PDO("mysql:host=$host;dbname=$name;charset=utf8", $user, $pass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    //////////////////////
    //      CODES       //
    //////////////////////
    
    /**
     * Tells if a code is available.
     *
     * @param string $code A citizen's code
     * @return bool TRUE if the code is still available, FALSE if not
     */
    public function codeAvailable($code)
    {
        $codes = $this->pdo->query("SELECT code FROM citizen")->fetchAll(PDO::FETCH_COLUMN);
        return !in_array($code, $codes);
    }
    
    /**
     * Returns all codes known by a specific citizen.
     *
     * @param int $id The ID of a valid citizen
     * @return array An array containing all known codes.
     */
    public function knownCodes($id)
    {
        $statements = [
            "SELECT DISTINCT sender_id FROM message WHERE receiver_id = ?",
            "SELECT DISTINCT receiver_id FROM message WHERE sender_id = ?",
            "SELECT DISTINCT buyer_id FROM transaction WHERE buyer_state = 0 AND seller_state = 0 AND seller_id = ?",
            "SELECT DISTINCT seller_id FROM transaction WHERE buyer_state = 0 AND seller_state = 0 AND buyer_id = ?",
            "SELECT DISTINCT citizen_id FROM authorized WHERE lock_id IN (SELECT id FROM `lock` WHERE owner_id = ?)",
        ];
        
        $codes = array();
        foreach($statements as $statement)
        {
            $st = $this->pdo->prepare($statement);
            $st->execute([$id]);
            foreach($st->fetchAll(PDO::FETCH_COLUMN) as $knownID)
                array_push($codes, $this->citizen($knownID)["code"]);
        }
        
        return array_unique($codes);
    }
    
    //////////////////////////
    //      CITIZENS        //
    //////////////////////////
    
    /**
     * Tells if a player is registered in the database.
     *
     * @param string $uuid The UUID of a player
     * @return bool TRUE if the player is registered, FALSE if not
     */
    public function isRegistered($uuid)
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM citizen WHERE player = ?");
        $st->execute([$uuid]);
        return $st->fetchColumn() > 0;
    }
    
    /**
     * Returns a citizen record.
     *
     * @param int $id The ID of a valid citizen
     * @return array An array containing all fields of the record
     */
    public function citizen($id)
    {
        $st = $this->pdo->prepare("SELECT * FROM citizen WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    /**
     * Returns the citizen record associated with the player's UUID.
     *
     * @param string $uuid The UUID of a registered player
     * @return array An array containing all fields of the record
     */
    public function citizenByUUID($uuid)
    {
        $st = $this->pdo->prepare("SELECT * FROM citizen WHERE player = ?");
        $st->execute([$uuid]);
        return $st->fetch();
    }
    
    /**
     * Returns the citizen record associated with a code.
     *
     * @param string $code The code of a valid citizen
     * @return array An array containing all fields of the record
     */
    public function citizenByCode($code)
    {
        $st = $this->pdo->prepare("SELECT * FROM citizen WHERE code = ?");
        $st->execute([strtoupper($code)]);
        return $st->fetch();
    }
    
    /**
     * Adds a new citizen to the database.
     *
     * @param string $code A unique citizen code
     * @param string $firstName The first name(s) of the citizen
     * @param string $lastName The last name(s) of the citizen
     * @param string $sex The sex of the citizen ("M" or "F")
     * @param int $stateID The ID of a valid state
     * @param string $player The UUID of a unregistered player
     * @return string The ID of the new citizen
     */
    public function addCitizen($code, $firstName, $lastName, $sex, $stateID, $player)
    {
        $st = $this->pdo->prepare(
            "INSERT INTO citizen (code, first_name, last_name, sex, state_id, balance, player) "
            ."VALUES (?, ?, ?, ?, ?, 0, ?)");
        $st->execute([$code, $firstName, $lastName, $sex, $stateID, $player]);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Tells if a citizen with a given code exists.
     *
     * @param string $code A valid or invalid code
     * @return bool TRUE if a citizen exists, FALSE if not
     */
    public function citizenExists($code)
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM citizen WHERE code = ?");
        $st->execute([strtoupper($code)]);
        return $st->fetchColumn() > 0;
    }
    
    //////////////////////
    //      STATES      //
    //////////////////////
    
    /**
     * Returns a list of all states.
     *
     * @return array An array containing all states
     */
    public function states()
    {
        return $this->pdo->query("SELECT * FROM state")->fetchAll();
    }
    
    /**
     * Returns a state record.
     *
     * @param int $id The ID of a valid state
     * @return array An array containing all fields of the record
     */
    public function state($id)
    {
        $st = $this->pdo->prepare("SELECT * FROM state WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    //////////////////////////
    //      MESSAGES        //
    //////////////////////////
    
    /**
     * Returns all conversations of a specific citizen.
     *
     * @param int $id The ID of a valid citizen
     * @return array An array containing all the most recent messages of every conversation
     */
    public function conversations($id)
    {
        $st = $this->pdo->prepare(
            "SELECT msg.* FROM message msg "
            ."INNER JOIN (SELECT MAX(timestamp) AS most_recent FROM message "
            ."GROUP BY LEAST(sender_id, receiver_id), GREATEST(sender_id, receiver_id)) group_msg "
            ."ON msg.timestamp = group_msg.most_recent "
            ."WHERE msg.sender_id = :id OR msg.receiver_id = :id "
            ."ORDER BY msg.timestamp DESC");
        
        $st->execute([":id" => $id]);
        return $st->fetchAll();
    }
    
    /**
     * Returns all messages of a conversation between two citizen.
     *
     * @param int $idA The ID of a first valid citizen
     * @param int $idB The ID of a second valid citizen
     * @return array An array containing all message from the conversation.
     */
    public function messages($idA, $idB)
    {
        $st = $this->pdo->prepare("SELECT * FROM message "
            ."WHERE sender_id = :idA AND receiver_id = :idB "
            ."OR sender_id = :idB AND receiver_id = :idA "
            ."ORDER BY timestamp");
        $st->execute([":idA" => $idA, ":idB" => $idB]);
        return $st->fetchAll();
    }
    
    /**
     * Returns the total number of messages sent or received by a specific citizen.
     *
     * @param int $id The ID of a valid citizen
     * @return int The total number of messages
     */
    public function messageCount($id)
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM message "
            ."WHERE sender_id = :id OR receiver_id = :id");
        $st->execute([":id" => $id]);
        return $st->fetchColumn();
    }
    
    /**
     * Returns the number of unread messages received by a specific citizen.
     *
     * @param int $receiverID The ID of the citizen receiving the messages
     * @return int The number of unread messages
     */
    public function unreadMessageCount($receiverID)
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM message "
            ."WHERE receiver_id = ? AND seen = 0");
        $st->execute([$receiverID]);
        return $st->fetchColumn();
    }
    
    /**
     * Returns the number of all unread messages sent by a specific citizen and
     * received by another specific citizen.
     *
     * @param int $senderID The ID of the citizen sending the messages
     * @param int $receiverID The ID of the citizen receiving the messages
     * @return int The number of unread messages
     */
    public function unreadMessageCountFrom($senderID, $receiverID)
    {
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM message "
            ."WHERE sender_id = ? AND receiver_id = ? AND seen = 0");
        $st->execute([$senderID, $receiverID]);
        return $st->fetchColumn();
    }
    
    /**
     * Adds a new message in the database.
     *
     * @param int $senderID The ID of the citizen sending the message
     * @param int $receiverID The ID of the citizen receiving the message
     * @param string $body The content of the message
     */
    public function addMessage($senderID, $receiverID, $body)
    {
        $st = $this->pdo->prepare(
            "INSERT INTO message (sender_id, receiver_id, body, timestamp) "
            ."VALUES (?, ?, ?, UNIX_TIMESTAMP(NOW()))");
        $st->execute([$senderID, $receiverID, $body]);
    }
    
    /**
     * Marks as read all messages sent by a specific citizen and
     * received by another specific citizen.
     *
     * @param int $senderID The ID of the citizen sending the messages
     * @param int $receiverID The ID of the citizen receiving the messages
     */
    public function readMessages($senderID, $receiverID)
    {
        $st = $this->pdo->prepare("UPDATE message SET seen = UNIX_TIMESTAMP(NOW()) ".
            "WHERE sender_id = ? AND receiver_id = ? AND seen = 0");
        $st->execute([$senderID, $receiverID]);
    }
    
    //////////////////////////////
    //      TRANSACTIONS        //
    //////////////////////////////
    
    /**
     * Returns all transactions of a specific citizen or state.
     *
     * @param int $id The ID of a valid citizen or state
     * @param bool $isState TRUE if the ID is for a state, FALSE for a citizen
     * @param string $sortBy Field used to sort the list
     * @return array An array containing all transactions
     */
    public function transactions($id, $isState, $sortBy = "timestamp")
    {
        // check for valid sorting
        $columns = array_column($this->pdo->query("DESCRIBE transaction")->fetchAll(), "Field");
        if(!in_array($sortBy, $columns))
            header("Location: ".parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
        
        $isState = intval($isState);
        
        $st = $this->pdo->prepare(
            "SELECT * FROM transaction "
            ."WHERE (buyer_id = :id AND buyer_state = $isState) "
            ."OR (seller_id = :id AND seller_state = $isState) "
            ."ORDER BY $sortBy DESC");
        $st->execute([":id" => $id]);
        return $st->fetchAll();
    }
    
    /**
     * Returns the total number of transactions for a specific citizen or state.
     *
     * @param int $id The ID of a valid citizen or state
     * @param bool $isState TRUE if the ID is for a state, FALSE for a citizen
     * @return int The total number of transactions
     */
    public function transactionCount($id, $isState)
    {
        $isState = intval($isState);
        
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM transaction "
            ."WHERE (buyer_id = :id AND buyer_state = $isState) "
            ."OR (seller_id = :id AND seller_state = $isState)");
        $st->execute([":id" => $id]);
        return $st->fetchColumn();
    }
    
    /**
     * Returns the number of all unread transactions of a specific citizen or state.
     *
     * @param int $id The ID of a valid citizen or state
     * @param bool $isState TRUE if the ID is for a state, FALSE for a citizen
     * @return int The number of unread transactions
     */
    public function unreadTransactionCount($id, $isState)
    {
        $isState = intval($isState);
        
        $st = $this->pdo->prepare("SELECT COUNT(*) FROM transaction "
            ."WHERE seller_id = ? AND seller_state = $isState AND seen = 0");
        $st->execute([$id]);
        return $st->fetchColumn();
    }
    
    /**
     * Adds a transaction to the database.
     *
     * @param int $buyerID The ID of the buyer (citizen or state)
     * @param bool $buyerState TRUE if the buyer ID is for a state, FALSE for a citizen
     * @param int $sellerID The ID of the seller (citizen or state)
     * @param bool $sellerState TRUE if the seller ID is for a state, FALSE for a citizen
     * @param int $amount The amount of money transacted
     * @param string $description A textual description of the transaction
     */
    public function addTransaction($buyerID, $buyerState, $sellerID, $sellerState, $amount, $description)
    {
        if($buyerState)
        {
            $buyerBalance = $this->state($buyerID)["balance"] - $amount;
            $buyerTable = "state";
        }
        else
        {
            $buyerBalance = $this->citizen($buyerID)["balance"] - $amount;
            $buyerTable = "citizen";
        }
        
        if($sellerState)
        {
            $sellerBalance = $this->state($sellerID)["balance"] + $amount;
            $sellerTable = "state";
        }
        else
        {
            $sellerBalance = $this->citizen($sellerID)["balance"] + $amount;
            $sellerTable = "citizen";
        }
        
        $this->pdo->beginTransaction();
        $st = $this->pdo->prepare(
            "INSERT INTO transaction (buyer_id, buyer_state, seller_id, seller_state, amount, description, timestamp) "
            ."VALUES (?, ?, ?, ?, ?, ?, UNIX_TIMESTAMP(NOW()))");
        $st->execute([$buyerID, $buyerState, $sellerID, $sellerState, $amount, $description]);
        
        $st = $this->pdo->prepare("UPDATE $buyerTable SET balance = ? WHERE id = ?");
        $st->execute([$buyerBalance, $buyerID]);
        
        $st = $this->pdo->prepare("UPDATE $sellerTable SET balance = ? WHERE id = ?");
        $st->execute([$sellerBalance, $sellerID]);
        
        $this->pdo->commit();
    }
    
    /**
     * Marks as read all transactions of a specific citizen or state.
     *
     * @param int $sellerID The ID of the seller citizen or state
     * @param bool $sellerState TRUE if the seller ID is for a state, FALSE for a citizen
     */
    public function readTransactions($sellerID, $sellerState)
    {
        $st = $this->pdo->prepare("UPDATE transaction SET seen = UNIX_TIMESTAMP(NOW()) "
            ."WHERE seller_id = ? AND seller_state = ? AND seen = 0");
        $st->execute([$sellerID, $sellerState]);
    }
    
    //////////////////////
    //      LOCKS       //
    //////////////////////
    
    /**
     * Returns all locks of a specific citizen.
     *
     * @param int $ownerID The ID of a valid citizen
     * @return array An array containing all locks
     */
    public function locks($ownerID)
    {
        $st = $this->pdo->prepare("SELECT * FROM `lock` WHERE owner_id = ?");
        $st->execute([$ownerID]);
        return $st->fetchAll();
    }
    
    /**
     * Returns all authorized citizens of a specific lock.
     *
     * @param int $lockID The ID of a valid lock
     * @return array An array containing all authorized citizens
     */
    public function authorized($lockID)
    {
        $st = $this->pdo->prepare(
            "SELECT * FROM citizen WHERE id IN (SELECT citizen_id FROM authorized WHERE lock_id = ?)");
        $st->execute([$lockID]);
        return $st->fetchAll();
    }
    
    /**
     * Authorizes a citizen to interact with a lock.
     *
     * @param int $lockID The ID of a valid lock
     * @param int $citizenID The ID of a valid citizen
     */
    public function addAuthorized($lockID, $citizenID)
    {
        if(!in_array($citizenID, $this->authorized($lockID)))
        {
            $st = $this->pdo->prepare("INSERT INTO authorized (lock_id, citizen_id) VALUES (?, ?)");
            $st->execute([$lockID, $citizenID]);
        }
    }
    
    /**
     * Removes a citizen's authorization to interact with a lock.
     *
     * @param int $lockID The ID of a valid lock
     * @param int $citizenID The ID of a valid citizen
     */
    public function removeAuthorized($lockID, $citizenID)
    {
        $st = $this->pdo->prepare("DELETE FROM authorized WHERE lock_id = ? AND citizen_id = ?");
        $st->execute([$lockID, $citizenID]);
    }
    
    //////////////////////
    //      COMPANY     //
    //////////////////////
    
    /**
     * Returns all companies that have a specific type of permissions.
     *
     * @param string $type The permissions type (government, press, bank)
     * @return array An array containing all companies
     */
    private function companies($type)
    {
        return $this->pdo->query(
            "SELECT id, name, description, founded FROM company "
            ."WHERE $type = TRUE AND (request, closed) = (FALSE, FALSE) "
            ."ORDER BY name")->fetchAll();
    }
    
    /**
     * Returns all companies that have government permissions.
     *
     * @return array An array containing all companies
     */
    public function governments()
    {
        return $this->companies("government");
    }
    
    /**
     * Returns all companies that have bank permissions.
     *
     * @return array An array containing all companies
     */
    public function banks()
    {
        return $this->companies("bank");
    }
    
    /**
     * Returns all companies that have press permissions.
     *
     * @return array An array containing all companies
     */
    public function presses()
    {
        return $this->companies("press");
    }
    
    /**
     * Returns all companies that don't have special permissions.
     *
     * @return array An array containing all companies
     */
    public function otherCompanies()
    {
        return $this->pdo->query(
            "SELECT id, name, description, founded FROM company "
            ."WHERE (government, bank, press, request, closed) = (FALSE, FALSE, FALSE, FALSE, FALSE) "
            ."ORDER BY name")->fetchAll();
    }
    
    /**
     * Returns all companies that have been closed.
     *
     * @return array An array containing all companies
     */
    public function closedCompanies()
    {
        return $this->pdo->query(
            "SELECT id, name, description, founded FROM company "
            ."WHERE request = FALSE AND closed != FALSE "
            ."ORDER BY name")->fetchAll();
    }
    
    /**
     * Return a company record.
     *
     * @param int $id The ID of a valid company
     * @return array An array containing all fields of the record
     */
    public function company($id)
    {
        $st = $this->pdo->prepare("SELECT * FROM company WHERE id = ?");
        $st->execute([$id]);
        return $st->fetch();
    }
    
    /**
     * Returns all citizens that are leaders of a specific company.
     *
     * @param int $companyID The ID of a valid company
     * @return array An array containing all leaders
     */
    public function leaders($companyID)
    {
        $st = $this->pdo->prepare(
            "SELECT * FROM citizen WHERE id IN "
            ."(SELECT citizen_id FROM worker WHERE company_id = ? AND leader = TRUE AND dismissed = FALSE)");
        $st->execute([$companyID]);
        return $st->fetchAll();
    }
    
    /**
     * Tells if a specific citizen is a leader of a specific company.
     *
     * @param int $citizenID The ID of a valid citizen
     * @param int $companyID The ID of a valid company
     * @return bool TRUE if the citizen is a leader, FALSE if not
     */
    public function isLeader($citizenID, $companyID)
    {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM worker "
            ."WHERE leader = TRUE AND citizen_id = ? AND company_id = ? AND dismissed = FALSE");
        
        $st->execute([$citizenID, $companyID]);
        return $st->fetchColumn() > 0;
    }
    
    /**
     * Tells if a specific citizen has governor permissions.
     *
     * @param int $citizenID The ID of a valid citizen
     * @return bool TRUE if the citizen is a governor, FALSE if not
     */
    public function isGovernor($citizenID)
    {
        $st = $this->pdo->prepare(
            "SELECT citizen_id FROM worker WHERE company_id IN "
            ."(SELECT id FROM company WHERE government = TRUE) "
            ."AND citizen_id = ? AND dismissed = FALSE");
        
        $st->execute([$citizenID]);
        return $st->fetchColumn() > 0;
    }
    
    /**
     * Returns the number of company requests in a specific state.
     *
     * @param int $stateID The ID of a valid state
     * @return int The number of requests
     */
    public function requestCount($stateID)
    {
        $st = $this->pdo->prepare(
            "SELECT COUNT(*) FROM company WHERE request = TRUE AND state_id = ?");
        
        $st->execute([$stateID]);
        return $st->fetchColumn();
    }
    
    /**
     * Returns all company requests in a specific state.
     *
     * @param int $stateID The ID of a valid state
     * @return array An array containing all requests
     */
    public function requests($stateID)
    {
        $st = $this->pdo->prepare(
            "SELECT id, name, founder_id, founded FROM company "
            ."WHERE request = TRUE AND state_id = ? ORDER BY founded DESC");
        
        $st->execute([$stateID]);
        return $st->fetchAll();
    }
    
    /**
     * Adds a new company request to the database.
     *
     * @param string $name The company name
     * @param string $description The company short description
     * @param string $presentation The company presentation
     * @param int $state_id The ID of a valid state
     * @param int $founder_id The ID of a valid citizen
     */
    public function addRequest($name, $description, $presentation, $state_id, $founder_id)
    {
        $this->pdo->beginTransaction();
        
        $st = $this->pdo->prepare(
            "INSERT INTO company (name, description, profession, presentation, state_id, founder_id, request, founded) "
            ."VALUES (?, ?, 'worker', ?, ?, ?, TRUE, UNIX_TIMESTAMP(NOW()))");
        $st->execute([$name, $description, $presentation, $state_id, $founder_id]);
        
        $st = $this->pdo->prepare(
            "INSERT INTO worker (company_id, citizen_id, leader, hired) "
            ."VALUES(LAST_INSERT_ID(), ?, FALSE, UNIX_TIMESTAMP(NOW()))");
        $st->execute([$founder_id]);
        
        $this->pdo->commit();
    }
    
    /**
     * Accepts or rejects a company request.
     *
     * @param int $id The ID of a valid company
     * @param bool $accept TRUE to accept the request, FALSE to reject
     */
    public function acceptRequest($id, $accept)
    {
        $this->pdo->beginTransaction();
        
        if($accept)
        {
            $st = $this->pdo->prepare("UPDATE company SET request = FALSE WHERE id = ?");
            $st->execute([$id]);
            
            $st = $this->pdo->prepare("UPDATE worker SET leader = TRUE WHERE company_id = ?");
            $st->execute([$id]);
        }
        else
        {
            $st = $this->pdo->prepare("DELETE FROM company WHERE id = ?");
            $st->execute([$id]);
            
            $st = $this->pdo->prepare("DELETE FROM worker WHERE company_id = ?");
            $st->execute([$id]);
        }
        
        $this->pdo->commit();
    }
    
    /**
     * Updates the informations of a specific company.
     *
     * @param int $id The ID of a valid company
     * @param string $name The company name
     * @param string $description The company short description
     * @param string $profession The profession name
     * @param string $presentation The company presentation
     */
    public function updateCompanyInformations($id, $name, $description, $profession, $presentation)
    {
        $st = $this->pdo->prepare(
            "UPDATE company SET name = ?, description = ?, profession = ?, presentation = ? WHERE id = ?");
        $st->execute([$name, $description, $profession, $presentation, $id]);
    }
    
    /**
     * Updates the permissions of a specific company.
     *
     * @param int $id The ID of a valid company
     * @param bool $government TRUE if the company has government permissions, FALSE if not
     * @param bool $bank TRUE if the company has bank permissions, FALSE if not
     * @param bool $press TRUE if the company has press permissions, FALSE if not
     * @param array $materials An array containing all materials that can be crafted by the company
     */
    public function updateCompanyPermissions($id, $government, $bank, $press, $materials)
    {
        $this->pdo->beginTransaction();
        
        $st = $this->pdo->prepare("UPDATE company SET government = ?, bank = ?, press = ? WHERE id = ?");
        $st->execute([$government, $bank, $press, $id]);
        
        $st = $this->pdo->prepare("DELETE FROM craft WHERE company_id = ?");
        $st->execute([$id]);
        
        $st = $this->pdo->prepare("INSERT INTO craft (company_id, material) VALUES(?, ?)");
        foreach($materials as $material)
            $st->execute([$id, $material]);
        
        $this->pdo->commit();
    }
    
    /**
     * Closes a company.
     *
     * @param int $id The ID of a valid company
     */
    public function closeCompany($id)
    {
        $this->pdo->beginTransaction();
        
        $st = $this->pdo->prepare("UPDATE company SET closed = UNIX_TIMESTAMP(NOW()) WHERE id = ?");
        $st->execute([$id]);
        
        $st = $this->pdo->prepare("UPDATE worker SET dismissed = UNIX_TIMESTAMP(NOW()) WHERE company_id = ?");
        $st->execute([$id]);
        
        $this->pdo->commit();
    }
    
    /**
     * Returns all materials that can be crafted by a specific company.
     *
     * @param int $companyID The ID of a valid company
     * @return array An array containing all craftable materials
     */
    public function materials($companyID)
    {
        $st = $this->pdo->prepare("SELECT material FROM craft WHERE company_id = ?");
        $st->execute([$companyID]);
        return $st->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Returns all workers from a specific company.
     *
     * @param int $companyID The ID of a valid company
     * @return array An array containing all workers
     */
    public function workers($companyID)
    {
        $st = $this->pdo->prepare("SELECT * FROM worker WHERE company_id = ? AND dismissed = FALSE");
        $st->execute([$companyID]);
        return $st->fetchAll();
    }
    
    /**
     * Hires a citizen in a specific company.
     *
     * @param int $company_id The ID of a valid company
     * @param int $citizen_id The ID of a valid citizen
     */
    public function hire($company_id, $citizen_id)
    {
        $st = $this->pdo->prepare(
            "INSERT INTO worker (company_id, citizen_id, hired) VALUES(?, ?, UNIX_TIMESTAMP(NOW()))");
        $st->execute([$company_id, $citizen_id]);
        
    }
    
    /**
     * Dismiss a worker from a specific company.
     *
     * @param int $id The ID of a valid worker
     */
    public function dismiss($id)
    {
        $st = $this->pdo->prepare("UPDATE worker SET dismissed = UNIX_TIMESTAMP(NOW()) WHERE id = ?");
        $st->execute([$id]);
    }
    
    /**
     * Promotes or demotes a specific worker.
     *
     * @param int $id The ID of a valid worker
     * @param boolean $leader TRUE to promote the worker, FALSE to demote
     */
    public function promote($id, $leader)
    {
        $st = $this->pdo->prepare("UPDATE worker SET leader = ? WHERE id = ?");
        $st->execute([$leader, $id]);
    }
}
