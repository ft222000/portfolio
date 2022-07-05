<?php
/**
 * Adds a new campus to the application. If there is a deleted entry with the same name, makes it active again.
 *
 * Accepts POST requests only.
 *
 * @param Name The name for the campus.
 * @param Long The longitude for the campus.
 * @param Lat The latitude for the campus.
 */

require_once("../util/Dbconnection.php");
require_once("../util/session.php");
//require_once("../util/verboseLogging.php");

// Query for checking if there is an existing campus.
$CHECK_EXISTING_QUERY = "SELECT C.Campus_ID, C.isDeleted FROM Campus as C WHERE C.Name LIKE ?";

// Query for updating and existing campus.
$UPDATE_EXISTING_QUERY = "UPDATE Campus SET isDeleted = 0, Longitude = ?, Latitude = ? WHERE Campus_ID = ?";

// Query for creating a new campus
$CREATE_CAMPUS_QUERY = "INSERT INTO `Campus` (`Name`, `Longitude`, `Latitude`) VALUES (?, ?, ?)";

// Variable for storing the JSON response
$response = new stdClass();

// Check what kind of request is being used
$method = $_SERVER['REQUEST_METHOD'];

// Ensure that the request came from a logged in user
if (isSustainabilityTeamUser($session_permission)) {
    switch ($method) {
        case 'POST':
            $name = $_POST['Name'];
            $long = $_POST['Long'];
            $lat = $_POST['Lat'];

            $campusAlreadyExists = 0;
            $ExistingCampusIsDeleted = 0;
            $idOfExistingCampus = -1;
            $error = "";

            // Check if the campus already exists.
            if($statement = $connection->prepare($CHECK_EXISTING_QUERY)) {
                if ($statement->bind_param("s", $name)) {
                    if ($statement->execute()) {
                        $result = $statement->get_result();
                        if ($result->num_rows > 0) {
                            $campusAlreadyExists = 1;
                            $row = $result->fetch_assoc();
                            $idOfExistingCampus = $row['Campus_ID'];
                            $ExistingCampusIsDeleted = $row['isDeleted'];
                        } else {
                            $campusAlreadyExists = 0;
                        }
                    } else {
                        $error = "Error executing the addition.";
                    }
                } else {
                    $error = "SQL Binding Error, Variables might not have been valid.";
                }
            } else {
                $error = "Issue preparing campus check.\n".$connection->error;
            }

            if ($error == "") {
                if ($campusAlreadyExists == 1) {
                    // Existing campus will be restored
                    if ($ExistingCampusIsDeleted == 0) {
                        // No change will be made.
                        $error = "The campus already exists";
                    } else {
                        // Update the campus
                        if($statement = $connection->prepare($UPDATE_EXISTING_QUERY)) {
                            if ($statement->bind_param("ssi", $long, $lat, $idOfExistingCampus)) {
                                if ($statement->execute()) {
                                    $response->wasSuccessful = 1;
                                    $response->message = "A previously deleted campus was restored.";
                                } else {
                                    $error = "Error executing the restoration of an old campus.";
                                }
                            } else {
                                $error = "SQL Binding Error, Variables might not have been valid.";
                            }
                        } else {
                            $error = "Issue preparing to restore previously deleted campus.";
                        }
                    }
                } else {
                    // Create a completely new campus.
                    if ($statement = $connection->prepare($CREATE_CAMPUS_QUERY)) {
                        if ($statement->bind_param("sss", $name, $long, $lat)) {
                            if ($statement->execute()) {
                                $response->wasSuccessful = 1;
                                $response->message = "The Campus was added successfully.";
                            } else {
                                $error = "Error executing the addition.";
                            }
                        } else {
                            $error = "SQL Binding Error, Variables might not have been valid.";
                        }
                    } else {
                        $error = "Issue preparing action.";
                    }
                }
            }

            if ($error != "") {
                $response->wasSuccessful = 0;
                $response->message = $error;
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