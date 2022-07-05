<?php
/**
 * Checks the permission level of a given user
 *
 * Accepts GET requests only.
 *
 * @param id The id of the user that you wish to receive information about.
 */
require_once("../util/Dbconnection.php");

// JN: Variable for storing the JSON response
$JSONresponse = new stdClass();

// JN: Variable for storing any error messages produced during execution
$error = "";

// JN: Check to see what kind of request is being used.
$method = $_SERVER['REQUEST_METHOD'];

// JN: Check to see that this request is a get request
if($method == 'GET') {

    if (isset($_GET["id"])) {
        $query = "SELECT HasPermissionLevel, IsDeleted, Activated FROM User WHERE User_ID = ?";
        $id = $_GET["id"];
        if($stmt = $connection->prepare($query)) {
            if($stmt->bind_param("i", $id)) {
                if ($stmt->execute()) {
                    if ($result = $stmt->get_result()) {
                        $row = $result->fetch_assoc();

                        $JSONresponse->wasSuccessful = 1;
                        $JSONresponse->message = "Result was provided in response.";
                        $JSONresponse->HasPermissionLevel = $row["HasPermissionLevel"];
                        $JSONresponse->IsDeleted = $row["IsDeleted"];
                        $JSONresponse->Activated = $row["Activated"];

                        $result->free();
                    } else {
                        $JSONresponse->wasSuccessful = 0;
                        $JSONresponse->message = "No result was provided.";
                    }
                }else {
                    $JSONresponse->wasSuccessful = 0;
                    $JSONresponse->message = "Error executing SQL statement";
                }
            }else {
                $JSONresponse->wasSuccessful = 0;
                $JSONresponse->message = "Error binding parameters to SQL statement";
            }
        }else {
            $JSONresponse->wasSuccessful = 0;
            $JSONresponse->message = "Error preparing the SQL statement.";
        }
    } else {
        $JSONresponse->wasSuccessful = 0;
        $JSONresponse->message = "No id was provided, unable to provide information.";
    }
} else {
    $JSONresponse->wasSuccessful = 0;
    $JSONresponse->message = "Request type was not allowed.";
}


// JN: Send the response in JSON format
$JSON = json_encode($JSONresponse);
echo $JSON;