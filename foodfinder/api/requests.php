<?php
/**
 * Allows the request page of the web portal to view and manage Event Organiser requests.
 *
 * Accepts both GET and POST requests.
 *
 * GET: Retrieves active requests from the database.
 * @param id Optionally limits the request to only the one needed
 *
 * POST: Allows for the approval or deletion of pending requests.
 * @param accept Used to determine if the request should be accepted.
 * @param decline Used to determine if the request should be declined.
 * @param requestID The ID of the user who made the request.
 *
 * Note: Either accept or decline should have a value, not both (Uses the isset() method to determine which option)
 *
 * Note: Requests can only be made after logging into the web portal (Relies on session data)
 */
require_once("../util/Dbconnection.php");
require_once("../util/session.php");
require_once('../util/requestManagement.php');

// JA: Gets the type of request received
$method = $_SERVER['REQUEST_METHOD'];

// JA: Ensure that the request came from a logged in user
if(isSustainabilityTeamUser($session_permission)) {
    // JA: Retrieve or send data to the database, depending on the request method
    switch ($method){
        // JA: Get all the users who have made a request
        case 'GET':
            $query = "SELECT User_ID, EmailAddress, RequestReason FROM User WHERE RequestedOrganiserPrivileges = 1 AND IsDeleted = 0";

            // JA: Check if a specific user's id is requested
            $specific = false;
            if (isset($_GET["id"])) {
                $id = filter_var($_GET["id"], FILTER_SANITIZE_NUMBER_INT);
                $query = $query . " AND User_ID = '$id'";
                $specific = true;
            }

            // JA: Variable to be populated with the retrieved data
            $response = [];

            if ($result = $connection->query($query)) {
                $i = 0;

                // JA: Populate the response variable with each result
                while ($row = $result->fetch_assoc()) {
                    $response[$i] = new stdClass();
                    $response[$i]->User_ID = $row["User_ID"];
                    $response[$i]->EmailAddress = $row["EmailAddress"];
                    if ($specific){
                        $response[$i]->RequestReason = $row["RequestReason"];
                    }
                    $i++;
                }
                // JA: Free the result
                $result->free();
            }

            // JA: Encode the response into JSON
            $JSON = json_encode($response);
            echo $JSON;
            break;

        // JA: Send the selected request's response to the database
        case 'POST':
            // JA: Permission level values
            $generalUser = 1;
            $eventOrganiser = 2;

            if (isset($_POST["accept"]) || isset($_POST["decline"]))
            {
                // JA: Check what the response was
                if (isset($_POST["accept"])) {
                    // JA: The request is accepted
                    $newPermissionLevel = $eventOrganiser;
                } elseif (isset($_POST["decline"])) {
                    // JA: The request is declined
                    $newPermissionLevel = $generalUser;
                }

                $query = GetUpdateStatement($_POST['requestID'], $newPermissionLevel);
                $connection->query($query) || die("Issues with database query");
                header("Location: ../requests.php");
            } else {
                header("Location: ../requests.php?error=bad_request");
            }
            break;
        default:
            echo "Bad Request";
    }
}