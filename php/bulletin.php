<?php

/**
 * Class Bulletin
 *
 * This class handles bulletins posted to site
 *
 * @author Charles Sandidge charles@cultivatingcoders.com
 **/

class Bulletin implements JsonSerializable
{

    /**
     *id for a bulletin this is primary key
     * @var int $bulletinId
     */
    private $bulletinId;
    /**
     * attaches key for user posting bulletin
     * @var int $userId foreign key for userId
     */
    private userId;
    /**
     * describes a bulletins category
     * @var string for category description of the bulletin
     */
    private $category;
    /**
     * the actual message content posted to the bulletin
     * @var string content of the bulletin message itself text only
     */
    private $message;


    /**
     * Bulletin constructor.
     * @param $newBulletinId
     * @param $newUserId
     * @param $newCategory
     * @param $newMessage
     */
    public function __construct($newBulletinId, $newUserId, $newCategory, $newMessage)
    {
        try {
            $this->setBulletinId($newBulletinId);
            $this->setUserId($newUserId);
            $this->setCategory($newCategory);
            $this->setMessage($newMessage);
        } catch (InvalidArgumentException $invalidArgument) {
            //rethrow the exception to the caller
            throw(new InvalidArgumentException($invalidArgument->getMessage(), 0, $invalidArgument));
        } catch (RangeException $range) {
            // rethrow the exception to the caller
            throw (new RangeException($range->getMessage(), 0, $range));
        } catch (Exception $exception) {
            // rethrow generic exception
            throw(new Exception($exception->getMessage(), 0, $exception));
        }
    }

    /**
     * accessor method for bulletinId
     *
     * @return int value of unique bulletinId
     **/
    public function getBulletinId()
    {
        return ($this->bulletinId);
    }

    /**
     * mutator method for the bulletinId
     *
     * @param int unique value to represent a user $newBulletinId
     * @throws InvalidArgumentException for invalid content
     **/
    public function setBulletinId($newBulletinId)
    {
        // base case: if the bulletinId is null,
        // this is a new bulletin without a mySQL assigned id (yet)
        if ($newBulletinId === null) {
            $this->bulletinId = null;
            return;
        }
        //verify the User is valid
        $newBulletinId = filter_var($newBulletinId, FILTER_VALIDATE_INT);
        if (empty($newBulletinId) === true) {
            throw (new InvalidArgumentException ("bulletinId invalid"));
        }
        $this->bulletinId = $newBulletinId;
    }

    /**
     * accessor method for userId
     *
     * @return int
     **/
    public function getUserId() {
        return ($this->userId);
    }

    /**
     * Mutator method for userId
     *
     * @param $newUserId int
     * @throws InvalidArgumentException if userId is invalid
     **/
    public function setUserId($newUserId) {
        // verify access level is integer
        $newUserId = filter_var($newUserId, FILTER_VALIDATE_INT);
        if(empty($newUserId) === true) {
            throw new InvalidArgumentException ("User Id Invalid");
        }
        $this->userId = $newUserId;
    }

    /**
     * accessor method for category
     *
     * @return string of category
     **/
    public function getCategory()
    {
        return ($this->category);
    }

    /**
     * Mutator method for Category
     *
     * @param string bulletin category $newCategory
     */
    public function setCategory($newCategory)
    {
        // verify category is valid
        $newCategory = filter_var($newCategory, FILTER_SANITIZE_STRING);
        if (empty($newCategory) === true) {
            throw new InvalidArgumentException("category invalid");
        }
        if (strlen($newCategory) > 32) {
            throw (new RangeException ("Category name too large"));
        }
        $this->category = $newCategory;
    }

    /**
     * accessor method for message
     *
     * @return string of message
     **/
    public function getMessage()
    {
        return ($this->message);
    }

    /**
     * Mutator method for Message
     *
     * @param string bulletin category $newMessage
     */
    public function setMessage($newMessage)
    {
        // verify message is valid
        $newMessage = filter_var($newMessage, FILTER_SANITIZE_STRING);
        if (empty($newMessage) === true) {
            throw new InvalidArgumentException("message invalid");
        }
        $this->category = $newMessage;
    }

    public function JsonSerialize()
    {
        $fields = get_object_vars($this);
        return ($fields);
    }

    /**
     * Inserts Bulletin into mySQL
     *
     * Inserts this bulletinId into mySQL in intervals
     * @param PDO $pdo connection to
     **/
    public function insert(PDO &$pdo)
    {
        // make sure bulletin doesn't already exist
        if ($this->bulletinId !== null) {
            throw (new PDOException("existing bulletin"));
        }
        //create query template
        $query
            = "INSERT INTO bulletin(userId, category, message)
		VALUES (:userId, :category, :message)";
        $statement = $pdo->prepare($query);

        // bind the variables to the place holders in the template
        $parameters = array("userId" => this->userId, "category" => $this->category, "message" => $this->message);
        $statement->execute($parameters);

        //update null bulletinId with what mySQL just gave us
        $this->bulletinId = intval($pdo->lastInsertId());
    }

    /**
     * Deletes Bulletin from mySQL
     *
     * Delete PDO to delete bulletinId
     * @param PDO $pdo
     **/
    public function delete(PDO &$pdo) {
        // enforce the bulletin is not null
        if($this->bulletinId === null) {
            throw(new PDOException("unable to delete a bulletin that does not exist"));
        }

        //create query template
        $query = "DELETE FROM bulletin WHERE bulletinId = :bulletinId";
        $statement = $pdo->prepare($query);

        //bind the member variables to the place holder in the template
        $parameters = array("bulletinId" => $this->bulletinId);
        $statement->execute($parameters);
    }

    /**
     * updates Message in mySQL
     *
     * Update PDO to update bulletin class
     * @param PDO $pdo pointer to PDO connection, by reference
     **/
    public function update(PDO $pdo) {

        // create query template
        $query = "UPDATE bulletin SET userId = :userId, category = :category, message = :message WHERE bulletinId = :bulletinId";
        $statement = $pdo->prepare($query);

        // bind the member variables
        $parameters = array("userId" => $this->userId, "category" => $this->category, "message" => $this->message,
             "bulletinId" => $this->bulletinId);
        $statement->execute($parameters);
    }

    /**
     * get bulletin by category
     *
     * @param PDO $pdo pointer to PDO connection, by reference
     * @param mixed info for $bulletin
     * @return null|Bulletin
     **/
    public static function getBulletinByCategory(PDO &$pdo, $bulletin) {
        if($bulletin === false) {
            throw(new PDOException(""));
        }
        // create query template
        $query = "SELECT bulletinId, userId, category, message
        FROM bulletin WHERE category = :category";
        $statement = $pdo->prepare($query);

        // bind the bulletinid to the place holder in the template
        $parameters = array("category" => $bulletin);
        $statement->execute($parameters);

        // grab the bulletin from mySQL
        try {
            $bulletin= null;
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $row = $statement->fetch();
            if($row !== false) {
                $bulletin = new Bulletin ($row["bulletinId"], $row["userId"], $row["category"], $row["message"]);
            }
        } catch(Exception $exception) {
            // if the row couldn't be converted, rethrow it
            throw(new PDOException($exception->getMessage(), 0, $exception));
        }
        return ($bulletin);
    }

    /**
     * Get all Bulletins
     *
     * @param PDO $pdo pointer to PDO connection, by reference
     * @return mixed|Bulletin
     **/
    public static function getAllBulletins(PDO &$pdo) {

        // create query template
        $query = "SELECT bulletinId, userId, category, message FROM bulletin";
        $statement = $pdo->prepare($query);

        // grab the bulletin from mySQL
        try {
            $user = null;
            $statement->setFetchMode(PDO::FETCH_ASSOC);
            $row = $statement->fetch();
            if($row !== false) {
                $bulletin = new Bulletin ($row["bulletinId"], $row["userId"], $row["category"], $row["message"]);
            }
        } catch(Exception $exception) {
            // if the row couldn't be converted, rethrow it
            throw(new PDOException($exception->getMessage(), 0, $exception));
        }
        return ($bulletin);
    }
}