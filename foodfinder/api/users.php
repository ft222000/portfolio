<?php
/**
 * Provides information on users, and facilitates the creation and updating of their information.
 *
 * Accepts GET and POST requests.
 *
 * GET: Returns a list of available users.
 *
 * GET: Lists detailed information about a specific user.
 * @param id The id of the user that you wish to receive information about.
 *
 * POST: Creates a new user.
 * @param EmailAddress The email address for the new user.
 * @param PermissionLevel The permission level that the user will be created at.
 * @param Password The password that will be set for this user.
 * @param PrimaryCampus The id of the campus that this user will be on.
 *
 * Note: Updating an existing user, or a previously deleted user, will overwrite all of their current values for these fields.
 */
require_once("../util/Dbconnection.php");
require_once("../util/session.php");
require_once("../util/userCreation.php");

// JN: Variable for storing the JSON response
$JSONresponse = new stdClass();

// JN: Variable for storing any error messages produced during execution
$error = "";

// JN: Check to see what kind of request is being used.
$method = $_SERVER['REQUEST_METHOD'];

// JN: Check to see if this request came from a logged in user (Prevents cross domain requests)
if(IsSustainabilityTeamUser($session_permission)) {
    // JN: Handles different types of HTTP requests
    switch ($method) {
        case 'GET':
            // JN: Variable for JSON response
            $response = new stdClass();

            // JN: Check to see if additional parameters have been set
            $id = -1;
            $detailed = false;
            if (isset($_GET["id"])) {
                // JN: Change the query to suit a details query
                $query = "SELECT * FROM User WHERE User_ID = ? AND IsDeleted = FALSE";
                $id = $_GET["id"];
                $detailed = true;
                $stmt = $connection->prepare($query);
            } else { //  JN: Is request for the user list
                $query = "SELECT User_ID, EmailAddress, Created, HasPermissionLevel FROM User WHERE IsDeleted = FALSE";
                $stmt = $connection->prepare($query);
            }

            // JN: Check for errors in prepare
            if(!$stmt) {
                $error = $stmt->error;
            } else { // JN: There was no error
                if ($detailed) {
                    if (!($stmt->bind_param("i", $id))) { // JN: Try to bind the parameter, if fails record error
                        $error = $stmt->error;
                    }
                }

                // JN: Was successful, execute
                if ($error == "") {
                    if (!$stmt->execute()) {
                        $error = $error . $stmt->error;
                    }
                }
            }

            // JN: Variable to be converted to JSON
            $response = [];
            if ($error == "") {
                if ($result = $stmt->get_result()) {
                    $i = 0; // JN: Iterator

                    // JN: Go through rows in the result, and add them to our response object
                    while ($row = $result->fetch_assoc()) {

                        // JN: Initialise object for JSON response
                        $response[$i] = new stdClass();

                        // JN: For basic response
                        $response[$i]->User_ID = $row["User_ID"];
                        $response[$i]->EmailAddress = $row["EmailAddress"];
                        $response[$i]->HasPermissionLevel = $row["HasPermissionLevel"];
                        $response[$i]->Created = $row["Created"];

                        // JN: Additional details for a specific response
                        if ($detailed) {
                            $response[$i]->RequestedOrganiserPrivileges = $row["RequestedOrganiserPrivileges"];
                            $response[$i]->RequestReason = $row["RequestReason"];
                            $response[$i]->IsDeleted = $row["IsDeleted"];
                            $response[$i]->Activated = $row["Activated"];
                            $response[$i]->EOApprover = $row["EOApprover"];
                            $response[$i]->PrimaryCampus = $row["PrimaryCampus"];
                        }

                        // JN: Goto the next record
                        $i++;
                    }
                    $result->free();
                }
            }

            // JN: Encode the array into JSON and respond
            if ($error == "") {
                $JSONresponse->wasSuccessful = 1;
                $JSONresponse->message = "Successfully got user(s).";
                $JSONresponse->data = $response;
            } else {
                $JSONresponse->wasSuccessful = 0;
                $JSONresponse->message = "Was unable to load data";
            }
            break;
        case 'POST':
            // JN: This will be used to create a new user
            if (filter_var($_POST['EmailAddress'], FILTER_VALIDATE_EMAIL)  // JN: Check if the email address is valid
                && CheckPermissionLevel($_POST['PermissionLevel'])) // JN: Check if the permission level is valid
                {
                // JN: Email has already been checked in previous if statement
                $fEmail = $_POST['EmailAddress'];

                // JN: Check if a user already exists
                if($stmt = $connection->prepare("SELECT User_ID, IsDeleted FROM User WHERE EmailAddress LIKE ?")) {
                    if($stmt->bind_param("s", $fEmail)) {
                        if ($stmt->execute()) {

                        } else {
                            $error = $stmt->error;
                        }
                    } else {
                        $error = $stmt->error;
                    }
                } else {
                    $error = $stmt->error;
                }

                if ($error == "") {
                    // JN: Get the result of the query
                    $result = $stmt->get_result();

                    // JN: -1 if no existing user is found
                    $id = -1;
                    $isDeleted = 0;
                    // JN: Check the database for existing user with the same email address as the new user
                    if (mysqli_num_rows($result) > 0) {
                        $row = $result->fetch_assoc();
                        $id = $row["User_ID"];
                        $isDeleted = (int)$row["IsDeleted"];
                    }

                    // JN: prepare other variables
                    $salt = GetSalt();
                    $fPassword = hash('sha256', $salt . $_POST['Password']);
                    $fHasPermissionLevel = $_POST['PermissionLevel']; // JN: Has already been validated
                    $fPrimaryCampus = $_POST['PrimaryCampus']; // JN: Has already been validated


                    if ($id < 0) { // JN: New user needs to be created
                        if($stmt = $connection->prepare("INSERT INTO `User` (`EmailAddress`, `Password`, `Salt`, `RequestedOrganiserPrivileges`, `RequestReason`, `IsDeleted`, `Activated`, `EOApprover`, `HasPermissionLevel`, `PrimaryCampus`) VALUES (?, ?, ?, '0', NULL, '0', '1',NULL, ?, ?)")) {
                            if($stmt->bind_param("sssii", $fEmail, $fPassword, $salt, $fHasPermissionLevel, $fPrimaryCampus)) {
                                if($stmt->execute()) {
                                    $stmt->close();
                                    $JSONresponse->wasSuccessful = 1;
                                    $JSONresponse->message = "The creation was successful";
                                } else {
                                    $error = $error.$stmt->error;
                                }
                            } else {
                                $error = $error.$stmt->error;
                            }
                        } else {
                            $error = $error.$stmt->error;
                        }
                    } else { // JN: Update existing user
                        if ($isDeleted == 1) {
                            if ($stmt = $connection->prepare("UPDATE `User` SET `Password` = ?, `Salt` = ?, isDeleted = false, `HasPermissionLevel` = ?, `PrimaryCampus` = ?, `Activated` = 1 WHERE `User_ID` = ?")) {
                                if ($stmt->bind_param("ssiii", $fPassword, $salt, $fHasPermissionLevel, $fPrimaryCampus, $id)) {
                                    if ($stmt->execute()) {
                                        $stmt->close();
                                        $JSONresponse->wasSuccessful = 1;
                                        $JSONresponse->message = "The user was restored from an existing user";
                                    } else {
                                        $error = $error . $stmt->error;
                                    }
                                } else {
                                    $error = $error . $stmt->error;
                                }
                            } else {
                                $error = $error . $stmt->error;
                            }
                        } else {
                            $JSONresponse->wasSuccessful = 1;
                            $JSONresponse->message = "This user is already active.";
                        }
                    }

                } else {
                    // JN: There was an error checking for existing users
                    $stmt->close();
                    $JSONresponse->wasSuccessful = 0;
                    $JSONresponse->message = "Existing user check.\n".$error;
                }
            } else {
                // JN: There was a problem with some of the variables that were passed through
                $JSONresponse->wasSuccessful = 0;
                $JSONresponse->message = "Bad validation of variables";
                //header("Location: ../users.php?error=bad_request_validation");
                break;
            }
            break;
        default:
            // JN: Not sure how to respond
            $JSONresponse->wasSuccessful = 0;
            $JSONresponse->message = "Bad request\n".$_SERVER["REQUEST_METHOD"];
    }
}

// JN: Send the response in JSON format
$JSON = json_encode($JSONresponse);
echo $JSON;