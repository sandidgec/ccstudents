<?php
require_once(dirname(dirname(__DIR__)) . "php/autoload.php");
/**require_once(dirname(dirname(__DIR__)) . "php/lib/xsrf.php");*/


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

    // sanitize the userId
    $userId = filter_input(INPUT_GET, "userId", FILTER_VALIDATE_INT);

    // sanitize the email
    $email = filter_input(INPUT_GET, "email", FILTER_SANITIZE_EMAIL);


    // grab the mySQL connection
    $pdo = connectToEncryptedMySql("/etc/apache2/ccstudents-mysql/ccstudents.ini");


    // handle all RESTful calls to User today
    // get some or all Users
    if($method === "GET") {
        // set an XSRF cookie on GET requests
        setXsrfCookie("/");
        if(empty($userId) === false) {
            $reply->data = User::getUserByUserId($pdo, $userId);
        } else if(empty($email) === false) {
            $reply->data = User::getUserByEmail($pdo, $email);
        } else {
            $reply->data = User::getAllUsers($pdo);
        }

        // post to a new User
    } else if($method === "POST") {
        // convert POSTed JSON to an object
        verifyXsrf();
        $requestContent = file_get_contents("php://input");
        $requestObject = json_decode($requestContent);

        if($requestObject->password !== $requestObject->passwordConfirm) {
            throw(new InvalidArgumentException("passwords do not match", 400));
        }

        $salt = bin2hex(openssl_random_pseudo_bytes(32));
        $hash = hash_pbkdf2("sha512", $requestObject->password, $salt, 262144, 128);

        // handle optional fields
        $activation = (empty($requestObject->activation) === true ? null : $requestObject->activation);


        $user = new User($userId, $requestObject->accessLevelId, false, $activation,
            $requestObject->email, $requestObject->firstName, $hash,
            $requestObject->lastName, $requestObject->phone, $requestObject->profilePath, $salt);
        $user->insert($pdo);
        $_SESSION["user"] = $user;
        $reply->data = "User created OK";

        // delete an existing User
    } else if($method === "DELETE") {
        verifyXsrf();
        $user = User::getUserByUserId($pdo, $userId);
        $user->delete($pdo);
        $reply->data = "User deleted OK";

        // put to an existing User
    } else if($method === "PUT") {
        // convert PUTed JSON to an object
        verifyXsrf();
        $requestContent = file_get_contents("php://input");
        $requestObject = json_decode($requestContent);
        $salt = bin2hex(openssl_random_pseudo_bytes(32));
        $hash = hash_pbkdf2("sha512", $requestObject->password, $salt, 262144, 128);

        $user = new User($userId, $requestObject->accessLevelId, $requestObject->activation, $requestObject->email,
            $requestObject->firstName, $hash, $requestObject->lastName, $requestObject->phone, $requestObject->profilePath,
            $salt);
        $user->update($pdo);
        $reply->data = "User updated OK";
    }

    // create an exception to pass back to the RESTful caller
} catch(Exception $exception) {
    $reply->status = $exception->getCode();
    $reply->message = $exception->getMessage();
    unset($reply->data);
}

header("Content-type: application/json");
echo json_encode($reply);