<?php 	
/**
 * Loads all active events for the mobile application.
 * If the user is an event organiser, this script loads all events that they have created that have not started yet.
 *	
 * Accepts POST Requests Only.
 *	
 * @param userId The user the events are being loaded for.
 */
require_once("../util/Dbconnection.php");

$method = $_SERVER['REQUEST_METHOD'];

$response = new stdClass();	

if($method == "POST"){		
	
	$userId = $_POST['userId'];
	
	/*TE: Query to load events for mobile application. Also loads events that the user has created that have not started so they can be edited.
      Since they are in the future, we sort by start time DESC so the Organisers events are at the top.
    */ 
	$eventsQuery = "SELECT 
					Event.Event_ID AS id,
					Event.Name AS name,
					Room.Name AS room,
					Event.Longitude AS Longitude,
					Event.Latitude AS Latitude,
					Campus.Campus_ID,
					Campus.Name AS campusName,
					Event.StartTime AS startTime,
					Event.FoodServeTime AS foodServeTime,
					Event.EventClosed AS closingTime,
					Event.Organiser,
					IFNULL(Event.SuggestedToClose, '-1') AS SuggestedToClose,
					IFNULL(FoodTag.Description, 'No Tags') AS tag						
				FROM Event
					INNER JOIN Room ON Room.Room_ID = Event.LocatedIn
					INNER JOIN Campus ON Campus.Campus_ID = Room.Campus
					LEFT JOIN Describes ON Describes.Event_ID = Event.Event_ID
					LEFT JOIN FoodTag ON FoodTag.Tag_ID = Describes.Tag_ID
				WHERE
					((Event.StartTime <= NOW() AND NOW() < Event.EventClosed)
					OR
					(Event.Organiser = ? AND Event.StartTime > NOW()))
					AND
					Event.Deleted = 0
				ORDER BY Event.StartTime DESC";
	
	/*TE: Prepare the query and execute it*/
	if($loadEventsStatement = $connection->prepare($eventsQuery)){
		if($loadEventsStatement->bind_param("i",$userId)){
			if($loadEventsStatement->execute()){
				
				$result = $loadEventsStatement->get_result();
				
				/*TE: This is the array that will contain all our query results*/
				$allEventsArray = array();
				/*TE: This array will hold our event until all tags have been added the to the tags array*/
				$currentEventDetails = NULL;
				/*TE: This is the id of the event we are processing. We keep adding tags to the tags array in the currentEventDetails array until this changes.
					  Events will be duplicated otherwise. I am sure there is a better way to do all of this.*/
				$currentEventId = NULL;		
				
				/*TE: Insert all the result rows into an array*/
				while($row = $result->fetch_assoc()){		
					
					/*TE: If we are on the same event still and we are not starting the loop, we have another food tag to add.*/
					if($currentEventId==$row['id'] && $currentEventId!=NULL){
						
						array_push($currentEventDetails['Tags'],$row['tag']);
						/*TE: We have a new event and all the tags have been added to the previous event. This is the start of the loop or a new event.*/   
					}else{
						/*TE: We do not want to add a null value to the events array. Could corrupt JSON. This filters out the starting case.*/
						if($currentEventDetails != NULL){
							$allEventsArray[]=$currentEventDetails;
						}
						
						/*TE: We overwrite or create new array with all the events details. An array is added for tags with the starting tag in it.*/
						$currentEventDetails=array("Id"=>$row['id'],
						"Name"=>$row['name'],
						"Location"=>$row['room'],
						"Longitude"=>$row['Longitude'],
						"Latitude"=>$row['Latitude'],
						"Campus"=>array("Id"=>$row['Campus_ID'],
						"Name"=>$row['campusName']),
						"StartTime"=>$row['startTime'],
						"FoodServeTime"=>$row['foodServeTime'],
						"ClosingTime"=>$row['closingTime'],
						"SuggestedToClose"=>$row['SuggestedToClose'],
						"Tags"=> array($row['tag']),
						"Organiser"=>$row['Organiser']);
						
						/*TE: We update the current event Id so we can check for loops in the next iteration if any.*/
						$currentEventId=$row['id'];
					}
				}
				/*TE: Once the loop reaches the end of the query rows, we add the last entry and it's array of tags to the EventsArray to send as JSON. Avoids NULL in the JSON.*/
				if($currentEventDetails != NULL){
					$allEventsArray[]=$currentEventDetails;
				}
				
				$response->wasSuccessful = 1;
				$response->events = $allEventsArray;
				$response->message = "Events loaded successfully.";
			}else{
				$response->wasSuccessful = 0;
				$response->message = "Could not execute statement to load events.";	
			}
		}else{
			$response->wasSuccessful = 0;
			$response->message = "Could not bind parameters to statement to load events.";	
		}
	}else{
		$response->wasSuccessful = 0;
		$response->message = "Could not prepare statement to load events.";	
	}	
}else{
	$response->wasSuccessful = 0;
	$response->message = "Events not loaded updated as post method was not used.";
}
$JSON = json_encode($response);
echo $JSON;
?>