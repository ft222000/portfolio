<?php
/**
 * Retrieves a list of Sustainability Team emails
 *
 * Accepts GET requests only.
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
        case 'GET':
            $permissionLevel = 3;
            $error = "";

            $query = "SELECT User_ID, EmailAddress, IsSubscribedToRequests FROM User WHERE HasPermissionLevel = ?";

            $stmt = $connection->prepare($query);
            $stmt->bind_param("i",$permissionLevel);

            if ($stmt->execute()){
                $response = [];

                if ($result = $stmt->get_result()) {
                    $i = 0;

                    while ($row = $result->fetch_assoc()){
                        $response[$i] = new stdClass();
                        $response[$i]->Id = $row["User_ID"];
                        $response[$i]->Email = $row["EmailAddress"];
                        $response[$i]->Sub = $row["IsSubscribedToRequests"];
                        $i++;
                    }

                    // Free the result
                    $result->free();
                } else {
                    $error .= $stmt->error;
                }
            } else {
                $error .= "\n couldn't execute query";
            }

            if ($error == "") {
                $response->wasSuccessful = 1;
                $response->message = "Retrieved Sustainability Team emails";
//                $response->data = $response;
            } else {
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