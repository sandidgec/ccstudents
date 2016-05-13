<?php
require_once(dirname(dirname(__DIR__)) . "php/autoload.php");
require_once(dirname(dirname(__DIR__)) . "php/xsrf.php");


// start the session and create a XSRF token
if(session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// prepare an empty reply
$reply = new stdClass();
$reply->status = 200;
$reply->data = null;

try {
    // determine which HTTP method was used
    $method = array_key_exists("HTTP_X_HTTP_METHOD", $_SERVER) ? $_SERVER["HTTP_X_HTTP_METHOD"] : $_SERVER["REQUEST_METHOD"];

    // sanitize the bulletinId
    $userId = filter_input(INPUT_GET, "bulletinId", FILTER_VALIDATE_INT);

    // grab the mySQL connection
   // $pdo = connectToEncryptedMySql("/etc/apache2/ninja-mysql/appsbyninja.ini");


    // handle all RESTful calls to Bulletin today
    // get some or all Bulletins
    if($method === "GET") {
        // set an XSRF cookie on GET requests
        setXsrfCookie("/");
        if(empty($userId) === false) {
            $reply->data = Bulletin::getBulletinByCategory($pdo, $bulletinId);
        } else if(empty($category) === false) {
            $reply->data = User::getBulletinByCategory($pdo, $category);
        } else {
            $reply->data = Bulletin::getAllBulletins($pdo);
        }

        // post to a new Bulletin
    } else if($method === "POST") {
        // convert POSTed JSON to an object
        verifyXsrf();
        $requestContent = file_get_contents("php://input");
        $requestObject = json_decode($requestContent);

        // handle optional fields
        $activation = (empty($requestObject->activation) === true ? null : $requestObject->activation);


        $bulletin = new Bulletin($bulletinId, $requestObject->userId, $requestObject->category, $requestObject->message);
        $bulletin->insert($pdo);
        $_SESSION["bulletin"] = $bulletin;
        $reply->data = "Bulletin created OK";

        // delete an existing Bulletin
    } else if($method === "DELETE") {
        verifyXsrf();
        $bulletin = Bulletin::getBulletinByBulletinId($pdo, $bulletinId);
        $bulletinId->delete($pdo);
        $reply->data = "Bulletin deleted OK";

        // put to an existing Bulletin
    } else if($method === "PUT") {
        // convert PUTed JSON to an object
        verifyXsrf();
        $requestContent = file_get_contents("php://input");
        $requestObject = json_decode($requestContent);

        $bulletin = new Bulletin($bulletinId, $requestObject->userId, $requestObject->category, $requestObject->message);
        $user->update($pdo);
        $reply->data = "Bulletin updated OK";
    }

    // create an exception to pass back to the RESTful caller
} catch(Exception $exception) {
    $reply->status = $exception->getCode();
    $reply->message = $exception->getMessage();
    unset($reply->data);
}

header("Content-type: application/json");
echo json_encode($reply);