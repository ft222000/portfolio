<?php
/**
 * Adds a new tag to the application. If there is a deleted entry with the same name, makes it active again.
 *
 * Accepts POST requests only.
 *
 * @param Name The name for the tag.
 */

require_once("../util/Dbconnection.php");
require_once("../util/session.php");
//require_once("../util/verboseLogging.php");

// Query for checking if there is an existing Tag.
$CHECK_EXISTING_QUERY = "SELECT T.Tag_ID, T.isDeleted FROM FoodTag as T WHERE T.Description LIKE ?";

// Query for updating and existing Tag.
$UPDATE_EXISTING_QUERY = "UPDATE FoodTag SET isDeleted = 0 WHERE Tag_ID = ?";

// Query for creating a new Tag
$CREATE_TAG_QUERY = "INSERT INTO `FoodTag` (`Description`, `isDeleted`) VALUES (?, 0)";

// Variable for storing the JSON response
$response = new stdClass();

// Check what kind of request is being used
$method = $_SERVER['REQUEST_METHOD'];

// Ensure that the request came from a logged in user
if (isSustainabilityTeamUser($session_permission)) {
    switch ($method) {
        case 'POST':
            $name = $_POST['Name'];

            $tagAlreadyExists = 0;
            $ExistingTagIsDeleted = 0;
            $idOfExistingTag = -1;
            $error = "";

            // Check if the Tag already exists.
            if($statement = $connection->prepare($CHECK_EXISTING_QUERY)) {
                if ($statement->bind_param("s", $name)) {
                    if ($statement->execute()) {
                        $result = $statement->get_result();
                        if ($result->num_rows > 0) {
                            $tagAlreadyExists = 1;
                            $row = $result->fetch_assoc();
                            $idOfExistingTag = $row['Tag_ID'];
                            $ExistingTagIsDeleted = $row['isDeleted'];
                        } else {
                            $tagAlreadyExists = 0;
                        }
                    } else {
                        $error = "Error executing the addition.";
                    }
                } else {
                    $error = "SQL Binding Error, Variables might not have been valid.";
                }
            } else {
                $error = "Issue preparing tag check.\n".$connection->error;
            }

            if ($error == "") {
                if ($tagAlreadyExists == 1) {
                    // Existing Tag will be restored
                    if ($ExistingTagIsDeleted == 0) {
                        // No change will be made.
                        $error = "The tag already exists";
                    } else {
                        // Update the Tag
                        if($statement = $connection->prepare($UPDATE_EXISTING_QUERY)) {
                            if ($statement->bind_param("i", $idOfExistingTag)) {
                                if ($statement->execute()) {
                                    $response->wasSuccessful = 1;
                                    $response->message = "A previously deleted tag was restored.";
                                } else {
                                    $error = "Error executing the restoration of an old tag.";
                                }
                            } else {
                                $error = "SQL Binding Error, Variables might not have been valid.";
                            }
                        } else {
                            $error = "Issue preparing to restore previously deleted tag.";
                        }
                    }
                } else {
                    // Create a completely new Tag.
                    if ($statement = $connection->prepare($CREATE_TAG_QUERY)) {
                        if ($statement->bind_param("s", $name)) {
                            if ($statement->execute()) {
                                $response->wasSuccessful = 1;
                                $response->message = "The Tag was added successfully.";
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