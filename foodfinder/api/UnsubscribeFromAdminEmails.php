<?php
/**
 * Allows the admin page of the web portal to unsubscribe a Sustainability Team email.
 *
 * Accepts POST requests only.
 *
 * @param id The id of the user that should be unsubscribed.
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
            $newSubState = 0;
            $email = $_POST['id'];

            $stmt = $connection->prepare("UPDATE User SET IsSubscribedToRequests = ? WHERE User_ID = ?");
            $stmt->bind_param("is",$newSubState,$email);
            if ($stmt->execute()){
                $response->wasSuccessful = 1;
                $response->message = "User was unsubscribed from request emails";
            } else {
                $response->wasSuccessful = 0;
                $response->message = "There was an issue usubscribing the user";
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