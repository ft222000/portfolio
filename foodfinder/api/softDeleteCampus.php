<?php
/**
 * Marks a campus as deleted in the database. The campus is not actually deleted, allowing existing events and report data to continue working.
 *
 * Accepts POST requests only.
 *
 * @param id The id of the campus that should be soft deleted.
 *
 * Note: Admin options can only be managed after logging into the web portal (Relies on session data)
 */
require_once("../util/Dbconnection.php");
require_once("../util/session.php");

// Variable for storing the JSON response
$response = new stdClass();

// Check what kind of request is being used
$method = $_SERVER['REQUEST_METHOD'];

// Ensure that the request came from a logged in user
if (isSustainabilityTeamUser($session_permission)) {
    switch ($method) {
        case 'POST':
            $newDeletedState = 1;
            $id = $_POST['id'];

            $stmt = $connection->prepare("UPDATE Campus SET isDeleted = ? WHERE Campus_ID = ?");
            $stmt->bind_param("is",$newDeletedState,$id);
            if ($stmt->execute()){
                $response->wasSuccessful = 1;
                $response->message = "Campus was removed from the application.";
            } else {
                $response->wasSuccessful = 0;
                $response->message = "There was an issue removing the campus";
            }
            break;
        default:
            // Unsure how to respond
            $response->wasSuccessful = 0;
            $response->message = "Bad request\n".$_SERVER["REQUEST_METHOD"];
    }
}

// Send the response in JSON format
$JSON = json_encode($response);
echo $JSON;