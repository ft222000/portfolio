<?php
/**
 * Allows the feedback page of the web portal to view and manage feedback items.
 *
 * Accepts both GET and POST requests.
 *
 * GET: Retrieves active feedback items from the database
 * @param id Optionally limits the feedback items to only the one needed
 *
 * POST: Allows the deletion of feedback items.
 * @param id The ID of the feedback item to be deleted.
 *
 * Note: Feedback can only be managed after logging into the web portal (Relies on session data)
 */
require_once("../util/Dbconnection.php");
require_once("../util/session.php");

// JA: Variable for storing the JSON response
$JSONresponse = new stdClass();

// JA: Variable for storing any error messages produced during execution
$error = "";

// JA: Check what kind of request is being used
$method = $_SERVER['REQUEST_METHOD'];

// JA: Ensure that the request came from a logged in user
if(isSustainabilityTeamUser($session_permission)) {
    // JA: Retrieve or send data to the database, depending on the request method
    switch ($method){
        // JA: Get all the users who have given feedback
        case 'GET':
            // JA: Variable for JSON response
            $response = new stdClass();

            $id = -1;
            $detailed = false;
            if (isset($_GET["id"])) {
                // JA: Update the query to suit a detailed query
                $selectQuery = "SELECT Feedback.Feedback_ID, Feedback.FeedbackMessage, Feedback.SubmittedTime, User.EmailAddress
                                FROM Feedback
                                INNER JOIN User
                                ON Feedback.User_ID = User.User_ID
                                WHERE Feedback.Feedback_ID = ? AND Feedback.IsDeleted = FALSE";
                $id = $_GET["id"];
                $detailed = true;
            } else { // JA: Request for the feedback list
                $selectQuery = "SELECT Feedback.Feedback_ID, User.EmailAddress
                                FROM Feedback
                                INNER JOIN User ON Feedback.User_ID = User.User_ID
                                WHERE Feedback.IsDeleted = FALSE";

            }
            $stmt = $connection->prepare($selectQuery);

            // JA: Check for errors in the prepared statement
            if (!$stmt) {
                $error = $stmt->error;
            } else { // JA: No errors
                if ($detailed) {
                    // JA: Try to bind the parameter, record error on fail
                    if (!($stmt->bind_param("i", $id))) {
                        $error = $stmt->error;
                    }
                }

                // JA: Was successful, execute
                if ($error == "") {
                    if (!$stmt->execute()) {
                        $error = $error.$stmt->error;
                    }
                }
            }

            // JA: Variable to be converted to JSON
            $response = [];

            if ($error == "") {
                if ($result = $stmt->get_result()) {
                    $i = 0; // JA: Iterator

                    // JA: Iterate through the result rows and add them to the response object
                    while ($row = $result->fetch_assoc()) {
                        // JA: Initialise the object for JSON response
                        $response[$i] = new stdClass();

                        // JA: Elements for a basic response
                        $response[$i]->Feedback_ID = $row["Feedback_ID"];
                        $response[$i]->EmailAddress = $row["EmailAddress"];
                        $response[$i]->SubmittedTime = $row["SubmittedTime"];
                        $response[$i]->IsDeleted = $row["IsDeleted"];

                        // JA: Additional details for a specific response
                        if ($detailed) {
                            $response[$i]->FeedbackMessage = $row["FeedbackMessage"];
                        }

                        // JA: Iterate to the next record
                        $i++;
                    }
                    $result->free();
                }
            }

            // JA: Encode the array into JSON and respond
            if ($error == "") {
                $JSONresponse->wasSuccessful = 1;
                $JSONresponse->message = "Successfully got feedback.";
                $JSONresponse->data = $response;
            } else {
                $JSONresponse->wasSuccessful = 0;
                $JSONresponse->message = "Was unable to load data";
            }
            break;
        // JA: Send the selected feedback item's response to the database
        case 'POST':
            $id = $_POST['id'];

            $stmt = $connection->prepare("UPDATE Feedback SET IsDeleted = TRUE WHERE Feedback_ID = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if ($stmt->error) {
                $error = $error.$stmt->error;
            }

            $stmt->close();

            // JA: Successful response
            if ($error == "") {
                $JSONresponse->wasSuccessful = 1;
                $JSONresponse->message = "Feedback item was deleted successfully!";
            } else {
                $JSONresponse->wasSuccessful = 0;
                $JSONresponse->message = $error;
            }
            break;
        default:
            // JA: Unsure how to respond
            $JSONresponse->wasSuccessful = 0;
            $JSONresponse->message = "Bad request\n".$_SERVER["REQUEST_METHOD"];
    }
}

// JA: Send the response in JSON format
$JSON = json_encode($JSONresponse);
echo $JSON;