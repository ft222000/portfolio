<?php
/**
 * @author Joeby Aaron Neil
 *
 * @param $idToDelete Passed into the api endpoint using 'is'
 */
require_once("../util/Dbconnection.php");
require_once("../util/session.php");

// JN: Response variable
$response = new stdClass();
$error = "";

// JN: Check if the logged in user has permission to use this feature
if(IsSustainabilityTeamUser($session_permission)) {
    // JN: Helper variable
    $idToDelete = $_POST['id'];

    // JN: Check to see if id is an int
    if (!filter_var($idToDelete, FILTER_VALIDATE_INT)) {

        // JN: Value was not an int
        $response->wasSuccessful = 0;
        $response->message = "The ID was not valid.";
    } else {
        // JN: Value was an int

        // JN: Set the user as deleted in the database
        $stmt = $connection->prepare("UPDATE `User` SET `IsDeleted` = TRUE WHERE `User_ID` = ?");
        $stmt->bind_param("i", $idToDelete);
        $stmt->execute();

        // JN: Check for errors
        if ($stmt->error) {
            $error = $error+$stmt->error;
        }

        $stmt->close();

        // JN: Successful response
        if ($error == "") {
            $response->wasSuccessful = 1;
            $response->message = "User was deleted successfully!";
        } else {
            $response->wasSuccessful = 0;
            $response->message = $error;
        }
    }
} else {
    // JN: Successful response
    $response->wasSuccessful = 0;
    $response->message = "You don't have access to this feature";
}

// JN: Encode and send response
$JSON = json_encode($response);
echo $JSON;