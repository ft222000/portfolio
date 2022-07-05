<?php
/**
 * Updates a a user with the given information.
 *
 * Accepts POST requests only.
 *
 * @param EmailAddress The email address of the user.
 * @param PermissionLevel The updated permission level for this user.
 * @param PrimaryCampus The updated primary campus for this user.
 * @param Activated The updated activation status of the user.
 * @param UserID The ID of the user being updated.
 */

require_once("../util/Dbconnection.php");
require_once("../util/session.php");
require_once("../util/userCreation.php");
require_once("../util/verboseLogging.php");

// JN: Check to see what kind of request is being used.
$method = $_SERVER['REQUEST_METHOD'];

// JN: Variable for storing the response back to the client before conversion
$response = new stdClass();

// JN: Check to see if this request came from a logged in user (Prevents cross domain requests)
if(IsSustainabilityTeamUser($session_permission)) {
    // JN: Handles different types of HTTP requests
    switch ($method) {
        case 'POST':
            // JN: Variable for checking if variables are valid, or if there is an error
            $variablesValid = true;
            $error = "";

            // JN: Check ID
            if (!filter_var($_POST['UserID'], FILTER_VALIDATE_INT)) {
                $variablesValid = false;
            }

            // JN: Check Email
            if (!filter_var($_POST['EmailAddress'], FILTER_VALIDATE_EMAIL)) {
                $error = $error . "Email was not valid.\n";
                $variablesValid = false;
            }

            // JN: Check Permission Level
            if (!CheckPermissionLevel($_POST["PermissionLevel"])) {
                $error = $error . "Permission Level was not valid.\n";
                $variablesValid = false;
            }

            // JN: All variables are valid
            if ($variablesValid) {
                
                // JN: Update the users information
                $stmt = $connection->prepare( "UPDATE `User` SET `EmailAddress` = ?, `HasPermissionLevel` = ?, `PrimaryCampus` = ?, `Activated` = ? WHERE `User_ID` = ?");
                $stmt->bind_param("siiii", $_POST["EmailAddress"],$_POST["PermissionLevel"], $_POST["PrimaryCampus"], $_POST["Activated"], $_POST["UserID"]);
                $stmt->execute();
                $stmt->close();

                // JN: Prepare JSON success packet
                $response->wasSuccessful = 1;
                $response->message = "User update was successful";
            } else {
                // JN: There was an issue with values
                $response->wasSuccessful = 0;
                $response->message = $error;
            }
            break;
        default:
            // JN: This file is unsure how to respond
            $response->wasSuccessful = 0;
            $response->message = "Invalid request for this API";
    }
}

// JN: Check to see if this request came from a logged in user (Prevents cross domain requests)
$JSON = json_encode($response);
echo $JSON;
