<?php
/**
 * Allows the admin page of the web portal to manage admin options.
 *
 * Accepts both GET and POST requests.
 *
 * GET: Retrieves selected admin options.
 * @param id Optionally limits the feedback items to only the one needed.
 *
 * POST: Allows the updating of admin options.
 * @param id The ID of the admin option to be updated.
 *
 * Note: Admin options can only be managed after logging into the web portal (Relies on session data)
 */
require_once("../util/Dbconnection.php");
require_once("../util/session.php");

// Variable for storing the JSON response
$JSONresponse = new stdClass();

// Variable for storing any error messages produced during execution
$error = "";

// Check what kind of request is being used
$method = $_SERVER['REQUEST_METHOD'];

// Ensure that the request came from a logged in user
if (isSustainabilityTeamUser($session_permission)) {
    switch ($method) {
        // Get the admin option selected
        case 'GET':
            // Variable for JSON response
            $response = new stdClass();

            $id = -1;

            // Variable to be converted to JSON
            $response = [];

            // Campuses option
            $response[0] = new stdClass();
            $response[0]->Option_ID = 1;
            $response[0]->Option_Name = "Campuses";

            // Food tags option
            $response[1] = new stdClass();
            $response[1]->Option_ID = 2;
            $response[1]->Option_Name = "Food tags";

            // Request emails option
            $response[2] = new stdClass();
            $response[2]->Option_ID = 3;
            $response[2]->Option_Name = "Request email subscriptions";


            if (isset($_GET["id"])) {
                $id = $_GET["id"] - 1;

                if ($error == "") {
                    $JSONresponse->wasSuccessful = 1;
                    $JSONresponse->message = "Successfully got selected admin option";
                    $JSONresponse->data = $response[$id];
                } else {
                    $JSONresponse->wasSuccessful = 0;
                    $JSONresponse->message = "Was unable to load data";
                }
            } else { // Return a list of the admin options
                if ($error == "") {
                    $JSONresponse->wasSuccessful = 1;
                    $JSONresponse->message = "Successfully got admin options";
                    $JSONresponse->data = $response;
                } else {
                    $JSONresponse->wasSuccessful = 0;
                    $JSONresponse->message = "Was unable to load data";
                }
            }
            break;
        // Handle updating admin options
        case 'POST':
            // TODO: Handle updating options
            break;
        default:
            // Unsure how to respond
            $JSONresponse->wasSuccessful = 0;
            $JSONresponse->message = "Bad request\n".$_SERVER["REQUEST_METHOD"];
    }

}

// Send the response in JSON format
$JSON = json_encode($JSONresponse);
echo $JSON;