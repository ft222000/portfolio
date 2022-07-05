<?php
/**
 * Created by PhpStorm.
 * User: Joeby Aaron Neil
 * Date: 21/5/18
 * Time: 3:09 PM
 *
 * Stores a new event in the database using the parameters given.
 *
 * Request Type: POST
 *
 * @param Name          The name of the event being created
 * @param EventClosed   The DateTime that the event should stop being displayed in the application. Deprecated.
 * @param FoodServeTime The original closing time of the event
 * @param Organiser     The user that is creating the event
 * @param StartTime     The DateTime that the event should start being displayed
 * @param ClosingTime   The DateTime that the event should stop being displayed in the application.
 * @param LocatedIn     The ID of the room that this event is being held in
 * @param Campus        The campus that this event will be taking place on.
 * @param Latitude      The Latitude postion of the event.
 * @param Longitude     The Longitude position of the event.
 */

require_once("../util/Dbconnection.php");
require_once("../util/userCreation.php");
// JN: Check to see what kind of request is being used.
$method = $_SERVER['REQUEST_METHOD'];

// JN: Object to hold response information
$response = new stdClass();

if($method == "POST") {

    // JN: Get variables
    $name = $_POST["Name"];
    if (isset($_POST["EventClosed"])) {
        $foodServeTime = $_POST["EventClosed"];
    } else if (isset($_POST["FoodServeTime"])) {
        $foodServeTime = $_POST["FoodServeTime"]; // This variable is currently being used as the close time
    }
    $organiser = $_POST["Organiser"];
    $eventStartTime = $_POST["StartTime"];
    if (isset($_POST["ClosingTime"])) {
        $eventClosingTime = $_POST["ClosingTime"]; //TE: Closing time is now being set properly
    } else {
        $eventClosingTime = $foodServeTime;
    }
    $locatedIn = $_POST["LocatedIn"];
    $campus = $_POST["Campus"];

    if (isset($_POST["Longitude"]) && isset($_POST["Latitude"])) {
        $longitude = $_POST["Longitude"];
        $latitude = $_POST["Latitude"];
    } else {
        $longitude = 147.122521;
        $latitude = -41.400756;
    }

    // JN: Check if there is an existing room and get its ID
    $roomID = DoesRoomExist($connection, $locatedIn, $campus);

    // JN: Send the room ID in the response for debugging
    // $response->roomID = $roomID;

    // JN: Create a new room if there is not one
    if ($roomID == -1) {

        // JN: Insert a new room. TE: Do not do lat and longitude here now!!!
        $roomQuery = "INSERT INTO `Room` (`Name`,`SISFM_Name`,`Campus`) VALUES (?,'Unused', ?)";
        $stmtRoom = $connection->prepare($roomQuery);
        $stmtRoom->bind_param("si",$locatedIn,$campus);
        $stmtRoom->execute();

        // JN: Get the id of the inserted entry
        $roomID = $stmtRoom->insert_id;
        $stmtRoom->close();
    }

    // JN: Expected variables for binding (Name, FoodServeTime, Organiser, LocatedIn)
    $eventsQuery = "INSERT INTO `Event` (`Name`, `StartTime`, `FoodServeTime`, `Organiser`, `LocatedIn`,`Latitude`,`Longitude`, `NotificationSent`, `EventClosed`) VALUES (?, ?, ?, ?, ?,?,?,0,?)";
    $stmt = $connection->prepare($eventsQuery);
    $stmt->bind_param("sssiidds", $name,$eventStartTime, $foodServeTime, $organiser, $roomID,$latitude,$longitude, $eventClosingTime);
    $stmt->execute();

    // JA: Get the ID of the last event
    $lastEventID = $stmt->$lastEventID.$connection->insert_id;


    // JN: Check for errors on the query and the connection
    $error = "";
    $error = $stmt->error.$connection->error;

    $stmt->close();

    if ($error == "") {
        // JN: Success response
        $response->wasSuccessful = 1;
        $response->message = "Event was created";
        $response->data = $lastEventID;
    } else {
        // JN: Bad response
        $response->wasSuccessful = 0;
        $response->message = $error;
    }

} else {
    // JN: Post method not used
    $response->wasSuccessful = 0;
    $response->message = "Event was not created, POST method not used";
}

/**
 * DoesRoomExist
 *
 * @PARAM connection    The connection to the database that has already been established
 * @PARAM name          The string of the room name
 * @PARAM campus        The campus the event is being created on
 * @RETURN id           The id of the room, or -1 if it doesn't exist
 */
function DoesRoomExist($connection, $name, $campus)
{
    // Check for an existing room
    $selectRoomQuery = "SELECT `Room_ID` FROM `Room` WHERE Campus = ? AND `Name` LIKE ?";
    $stmt = $connection->prepare($selectRoomQuery);
    $stmt->bind_param("is", $campus, $name);
    $stmt->execute();

    // Get result
    $result = $stmt->get_result();

    // Room or default value
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["Room_ID"];
    } else {
        return -1; // No room was found
    }
}

// JN: Check to see if this request came from a logged in user (Prevents cross domain requests)
$JSON = json_encode($response);
echo $JSON;