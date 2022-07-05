<?php
/**
 * Updates information about an event.
 *
 * Accepts POST requests only.
 *
 * @param Event_ID The ID of the event to be updated.
 * @param Name The name of the event.
 * @param EventClosed The time that you wish for the event to close.
 * @param Organiser The user ID of the organiser of the event.
 * @param StartTime The time that you wish for the event to start.
 * @param ClosingTime Deprecated: If not provided, will default to EventClosed.
 * @param LocatedIn The name of the room/building that the event will be located in.
 * @param Campus The ID of the campus that the event will be located on.
 * @param Longitude The Longitude of the pin to be displayed in the mobile application.
 * @param Latitude The Latitude of the pin to be displayed in the mobile application.
 */

require_once("../util/Dbconnection.php");
require_once("../util/userCreation.php");
require_once ("../util/verboseLogging.php");

// JN: Check to see what kind of request is being used.
$method = $_SERVER['REQUEST_METHOD'];

// JN: Object to hold response information
$response = new stdClass();

$error = "";

if($method == "POST"){

    // JN: Get variables
	/*TE: Added Event Id for update query*/
	$eventId = $_POST["Event_ID"];
    $name = $_POST["Name"];
    if (isset($_POST["EventClosed"])) {
        $foodServeTime = $_POST["EventClosed"];
    } else if(isset($_POST["FoodServeTime"])) {
        $foodServeTime = $_POST["FoodServeTime"]; // This variable is currently being used as the close time @deprecated
    }
    $organiser = $_POST["Organiser"];
    $eventStartTime = $_POST["StartTime"];
    if (isset($_POST["ClosingTime"])) {
        $eventCloseTime = $_POST["ClosingTime"]; //TE: Closing time is now being set properly
    } else {
        $eventCloseTime = $foodServeTime;
    }

    $locatedIn = $_POST["LocatedIn"];
    $campus = $_POST["Campus"];	

    // JN: Check if there is an existing room and get its ID
    $roomID = DoesRoomExist($connection, $locatedIn, $campus);

    // JN: Send the room ID in the response for debugging
    $response->roomID = $roomID;
    $response->campusID = $campus;

    // JN: Create a new room if there is not one
    if ($roomID == -1) {

        // JN: Insert a new room
        $roomQuery = "INSERT INTO `Room` (`Name`,`SISFM_Name`, `Campus`) VALUES (?, 'Unused', ?)";
        if($stmtRoom = $connection->prepare($roomQuery)) {
            if($stmtRoom->bind_param("si", $locatedIn, $campus)) {
                if($stmtRoom->execute()){} else {
                    $error = $error.$stmtRoom->error;
                }
            } else {
                $error = $error.$stmtRoom->error;
            }
        } else {
            $error = $error.$stmtRoom->error;
        }

        // JN: Get the id of the inserted entry
        $roomID = $stmtRoom->insert_id;
        $stmtRoom->close();
    }

    if ($error == "") {
		/*TE: We update the lattitude and longitude on the event now. This checking if isset stops a crash if the web application updates an event*/
		if(isset($_POST["Latitude"]) && isset($_POST["Longitude"])){
			
			$longitude = $_POST["Longitude"];
			$latitude = $_POST["Latitude"];
			
			/*TE: Query to update the event. Maybe change notificationSent back to 0 later so users are re-notified of changes?*/
			$updateQuery = "UPDATE `Event` SET `Name` = ?, `StartTime` = ?, `FoodServeTime` = ?, `Organiser` = ?, `LocatedIn` = ?, `Latitude` = ?, `Longitude` = ?, `EventClosed` = ? WHERE Event_ID = ? ";
			
			if($stmt = $connection->prepare($updateQuery)) {
				if($stmt->bind_param("sssiiddsi", $name, $eventStartTime, $foodServeTime, $organiser, $roomID, $latitude, $longitude, $eventCloseTime, $eventId)) {
					if($stmt->execute()){} else {
						$error = $error.$stmt->error;
					}
				}else {
					$error = $error.$stmt->error;
				}
			}else {
				$error = $error.$stmt->error;
			}
		}else{
			
			/*TE: Query to update the event. Maybe change notificationSent back to 0 later so users are re-notified of changes?*/
			$updateQuery = "UPDATE `Event` SET `Name` = ?, `StartTime` = ?, `FoodServeTime` = ?, `Organiser` = ?, `Longitude` = ?, `EventClosed` = ? WHERE Event_ID = ? ";
			
			if($stmt = $connection->prepare($updateQuery)) {
				if($stmt->bind_param("sssiisi", $name, $eventStartTime, $foodServeTime, $organiser, $roomID, $eventCloseTime, $eventId)) {
					if($stmt->execute()){} else {
						$error = $error.$stmt->error;
					}
				}else {
					$error = $error.$stmt->error;
				}
			}else {
				$error = $error.$stmt->error;
			}
		}

        $stmt->close();
    }

    // JN: Check for errors on the query and the connection
    $error = $error.$connection->error;


    if ($error == "") {
        // JN: Success response
        $response->wasSuccessful = 1;
        $response->message = "Event was Updated";
    } else {
        // JN: Bad response
        $response->wasSuccessful = 0;
        $response->message = $error;
    }

} else {
    // JN: Post method not used
    $response->wasSuccessful = 0;
    $response->message = "Event was not updated, POST method not used";
}

/* JN:
 * DoesRoomExist
 *
 * @PARAM name - The string of the room name
 * @PARAM campus - The campus the event is being created on
 * @RETURN id - The id of the room, or -1 if it doesn't exist
 */
function DoesRoomExist($connection, $name, $campus)
{
    // JN: Check for an existing room
    $selectRoomQuery = "SELECT `Room_ID` FROM `Room` WHERE Campus = ? AND `Name` LIKE ?";
    $stmt = $connection->prepare($selectRoomQuery);
    $stmt->bind_param("is", $campus, $name);
    $stmt->execute();

    // JN: Get result
    $result = $stmt->get_result();

    // JN: Room or default value
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row["Room_ID"];
    } else {
        return -1;
    }
}

// JN: Check to see if this request came from a logged in user (Prevents cross domain requests)
$JSON = json_encode($response);
echo $JSON;