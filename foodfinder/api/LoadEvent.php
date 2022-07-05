<?php
/**
 * Created by PhpStorm.
 * User: Kim
 * Date: 14/05/2018
 * Time: 6:05 PM
 *
 * This file provides access between the requests page and the database
 *
 * It will:
 * -Get EventOrganiser permissions requests from the database
 * -Send the selected event's response to the database
 */

require_once("../util/Dbconnection.php");
require_once("../util/session.php");
require_once('../util/requestManagement.php');

// JN: Response variable
$JSON_response = new stdClass();

$error = "";
// Kim: Ensure that the request came from a logged in user
if(isSustainabilityTeamUser($session_permission)) {
    // Kim: Gets the type of request received
    $method = $_SERVER['REQUEST_METHOD'];

    // Kim: Retrieve or send data to the database, depending on the request method
    switch ($method){
        // Kim: Get all the users who have made a request
        case 'GET':
            // JN: Check if an event ID has been provided
            if (isset($_GET["id"])) {
                $query = "SELECT E.Event_ID,
                              E.Name,
                              E.StartTime,
                              E.FoodServeTime,
                              E.EventClosed,
                              E.Organiser as Organiser_ID,
                              U.EmailAddress as Organiser,
                              E.LocatedIn as Location_ID,
                              R.Name as LocatedIn,
                              C.Name as Campus,
                              C.Campus_ID,
                              E.Longitude AS Longitude,
							  E.Latitude AS Latitude
                              FROM Event as E
                              INNER JOIN Room R on E.LocatedIn = R.Room_ID
                              INNER JOIN Campus C on R.Campus = C.Campus_ID
                              INNER JOIN User U on E.Organiser = U.User_ID
                        WHERE E.Event_ID = ?;";
                $stmt = $connection->prepare($query);
                $stmt->bind_param("i",$_GET["id"]);
            } else { // JN: General query
                $query = "SELECT * FROM Event WHERE `Deleted` = 0 ORDER BY StartTime DESC";
                $stmt = $connection->prepare($query);
            }

            if (!$stmt) {
                // Error
                $error = $error.$stmt->error;
            } {
                if ($stmt->execute()) {
                    // Kim: Variable to be populated with the retrieved data
                    $response = [];

                    if ($result = $stmt->get_result()) {
                        $i = 0;

                        // Kim: Populate the response variable with each result
                        while ($row = $result->fetch_assoc()) {
                            $response[$i] = new stdClass();
                            $response[$i]->Event_ID = $row["Event_ID"];
                            $response[$i]->Name = $row["Name"];
                            $response[$i]->StartTime = $row['StartTime'];
                            $response[$i]->FoodServeTime = $row['FoodServeTime'];
                            $response[$i]->EventClosed = $row['EventClosed'];
                            $response[$i]->Organiser = $row['Organiser'];
                            $response[$i]->LocatedIn = $row['LocatedIn'];
                            $response[$i]->Location_ID = $row['Location_ID'];
                            $response[$i]->Organiser_ID = $row['Organiser_ID'];
                            $response[$i]->Campus = $row['Campus'];
                            $response[$i]->Campus_ID = $row['Campus_ID'];
                            $response[$i]->Lat = $row['Latitude'];
                            $response[$i]->Long = $row['Longitude'];
                            // $response[$i]->SuggestedToClose = $row["SuggestedToClose"]; // JN: Removed from query
                            // $response[$i]->NotificationSent = $row["NotificationSent"]; // JN: Removed from query
                            $i++;
                        }

                        // Kim: Free the result
                        $result->free();
                    } else {
                        $error = $error . $stmt->error;
                    }
                } else {
                    $error = $error."\n couldn't execute query";
                }
            }

            // Kim: Encode the response into JSON
            if ($error == "") {
                $JSON_response->wasSuccessful = 1;
                $JSON_response->message = "Events were successfully loaded.";
                $JSON_response->data = $response;
            } else {
                $JSON_response->wasSuccessful = 0;
                $JSON_response->message = $error;
            }
            break;
        case 'POST':
            $update_request = false;
            // Check that all required values are provided (or have valid default values)

            // Check if a event id was provided
            // If so this is an update request
            if($update_request) {

            } else { // This was a creation request

            }

            // Check for errors

            // Send a response
            $JSON_response->wasSuccessful = 0;
            $JSON_response->message = "This functionality has not yet been implemented";
            break;
        default:
            $JSON_response->wasSuccessful = 0;
            $JSON_response->message = "Bad request";
    }
}

$JSON = json_encode($JSON_response);
echo $JSON;