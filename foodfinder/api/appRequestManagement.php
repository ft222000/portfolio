<?php
/**
 * User: Justin M Hood
 * Created Date: 21/05/2018
 * Time: 12:45 PM
 * Updated: 24/05/2018
 *
 * Updates a user's request status and request reason.
 *
 * Accepts POST requests only.
 *
 * @param User_ID - The user ID of the user.
 * @param RequestReason - The request reason of the user
 *
 */
require_once("../util/Dbconnection.php");

$method = $_SERVER['REQUEST_METHOD'];

$response = new stdClass();

if($method == "POST"){

    // JH: Get variables
    $userID = $_POST["User_ID"];
    $requestReason = $_POST["RequestReason"];

    // JH: Set up the query and execute
    $query = "UPDATE `User` SET `RequestedOrganiserPrivileges` = 1, `RequestReason` = ? WHERE `User_ID` = ?";
    $stmtRequest = $connection->prepare($query);
    $stmtRequest->bind_param("si", $requestReason, $userID);
    $stmtRequest->execute();
    
    // JH: Check for errors on the query and the connection
    $error = "";
    $error = $stmtRequest->error.$connection->error;
    $stmtRequest->close();
    
    if ($error == "") {
        // JN: Success response
        $response->wasSuccessful = 1;
        $response->message = "Request sent";
    } else {
        // JN: Bad response
        $response->wasSuccessful = 0;
        $response->message = $error;
    }
}

// JA: Encode and return the response as JSON
$JSON = json_encode($response);
echo $JSON;