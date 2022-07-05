<?php
/**
 * Created by PhpStorm.
 * User: Joeby Aaron Neil
 * Date: 24/07/2018
 * Time: 7:05 PM
 *
 * Takes a post request containing the users id, and sets their account as deleted
 * Deletes a user from the application.
 * The user is not actually removed from the database, instead setting their deleted value to TRUE
 *
 * @param id    The id of the user to be deleted.
 */
require_once("../util/Dbconnection.php");
require_once("../util/session.php");

// JN: Delete query variable
$DELETE_QUERY = "UPDATE `Event` SET `Deleted` = TRUE WHERE `Event_ID` = ?";

// JN: Response variable
$response = new stdClass();

// JN: Response variable for errors
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

        // JN: Delete the event
        if($stmt = $connection->prepare($DELETE_QUERY)) {
            if($stmt->bind_param("i", $idToDelete)) {
                if($stmt->execute()){

                } else {
                    $error = $stmt->error;
                }
            } else {
                $error = $stmt->error;
            }
        } else {
            $error = $stmt->error;
        }
        $stmt->close();

        // JN: Successful response
        if ($error == "") {
            $response->wasSuccessful = 1;
            $response->message = "Event was deleted successfully!";
        } else {
            $response->wasSuccessful = 0;
            $response->message = "Event deletion failed:\n".$error;
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