<?php

/**
 * Class Bulletin
 *
 * This class handles bulletins posted to site
 *
 * @author Charles Sandidge charles@cultivatingcoders.com
 **/

class Bulletin implements JsonSerializable {

    /**
     *id for a bulletin this is primary key
     * @var int $bulletinId
     */
    private $bulletinId;
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
     * timestamp of the content
     * @var timestamp of message posted to bulletin
     */
    private $timestamp;


    /**
     * Bulletin constructor.
     * @param $newBulletinId
     * @param $newCategory
     * @param $newMessage
     * @param $newTimestamp
     */
    public function __construct($newBulletinId, $newCategory, $newMessage, $newTimestamp)
    {
        try {
            $this->setBulletinId($newBulletinId);
            $this->setCategory($newCategory);
            $this->setMessage($newMessage);
            $this->setTimestamp($newTimestamp);
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
    public function getBulletinId() {
        return ($this->bulletinId);
    }

    /**
     * mutator method for the bulletinId
     *
     * @param int unique value to represent a user $newBulletinId
     * @throws InvalidArgumentException for invalid content
     **/
    public function setBulletinId($newBulletinId) {
        // base case: if the bulletinId is null,
        // this is a new bulletin without a mySQL assigned id (yet)
        if($newBulletinId === null) {
            $this->bulletinId = null;
            return;
        }
        //verify the User is valid
        $newBulletinId = filter_var($newBulletinId, FILTER_VALIDATE_INT);
        if(empty($newBulletinId) === true) {
            throw (new InvalidArgumentException ("bulletinId invalid"));
        }
        $this->bulletinId = $newBulletinId;
    }

    /**
     * accessor method for category
     *
     * @return string of category
     **/
    public function getCategory() {
        return ($this->category);
    }

    /**
     * Mutator method for Category
     *
     * @param string bulletin category $newCategory
     */
    public function setCategory($newCategory) {
        // verify category is valid
        $newCategory = filter_var($newCategory, FILTER_SANITIZE_STRING);
        if(empty($newCategory) === true) {
            throw new InvalidArgumentException("category invalid");
        }
        if(strlen($newCategory) > 32) {
            throw (new RangeException ("Category name too large"));
        }
        $this->category = $newCategory;
    }

    /**
     * accessor method for message
     *
     * @return string of message
     **/
    public function getMessage() {
        return ($this->message);
    }

    /**
     * Mutator method for Message
     *
     * @param string bulletin category $newMessage
     */
    public function setMessage($newMessage) {
        // verify message is valid
        $newMessage = filter_var($newMessage, FILTER_SANITIZE_STRING);
        if(empty($newMessage) === true) {
            throw new InvalidArgumentException("message invalid");
        }
        $this->category = $newMessage;
    }

    /**
     * accessor method for timestamp
     *
     * @return timestamp
     **/
    public function getTimestamp() {
        return ($this->timestamp);
    }

    /**
     * Mutator method for timestamp
     *
     * @param timestamp $newTimestamp
     */
    public function setTimestamp ($newTimestamp) {

        if(empty($newTimestamp) === true) {
            throw new InvalidArgumentException("timestamp invalid");
        }
        $this->timestamp = $newTimestamp;
    }

}