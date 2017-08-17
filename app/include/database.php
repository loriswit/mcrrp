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
        
        $host = "localhost";
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
     * @param int $balance The initial balance of the citizen
     * @param string $player The UUID of a unregistered player
     */
    public function addCitizen($code, $firstName, $lastName, $sex, $stateID, $balance, $player)
    {
        $st = $this->pdo->prepare(
            "INSERT INTO citizen (code, first_name, last_name, sex, state_id, balance, player) "
            ."VALUES (?, ?, ?, ?, ?, ?, ?)");
        $st->execute([$code, $firstName, $lastName, $sex, $stateID, $balance, $player]);
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
        $st = $this->pdo->prepare("UPDATE transaction SET seen = UNIX_TIMESTAMP(NOW()) ".
            "WHERE seller_id = ? AND seller_state = ? AND seen = 0");
        $st->execute([$sellerID, $sellerState]);
    }
}
