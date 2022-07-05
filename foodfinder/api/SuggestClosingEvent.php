<?php
/**
 * Sets the event suggested to close flag in the database, and sends a push notification to the Event Organiser.
 *
 * Accepts POST requests only.
 *
 * @param EventId The id of the event associated with the suggestion.
 * @param Closer The id of the user who is suggesting that the event be closed.
 * @param OrganiserId The id of the Event Organiser who will receive the push notification.
 * @param EventName The name of the event, which will be displayed on the push notification.
 */

require_once("../util/Dbconnection.php");

$method = $_SERVER['REQUEST_METHOD'];

$response = new stdClass();

if($method == "POST") {
	
	/*TE: Make sure variable is set. Don't want ot update it with an empty field*/
	if(isset($_POST["EventId"]) && isset($_POST["Closer"])){		
		
		$eventId = $_POST["EventId"];
		$closer = $_POST["Closer"];
		$organiser = $_POST["OrganiserId"];
		$eventName = $_POST["EventName"];
		
		$checkEventClosed = "SELECT IFNULL(SuggestedToClose,'-1') AS SuggestedToClose, Deleted FROM Event WHERE Event_ID = ?";
		
		/*TE: Check if event already closed or not etc. Capture any errors.*/
		if($stmtCheckEvent = $connection->prepare($checkEventClosed)){				
			if($stmtCheckEvent->bind_param("i",$eventId)){
				if($stmtCheckEvent->execute()){
					
					$checkEventResults = $stmtCheckEvent->get_result();
					$resultRow = $checkEventResults->fetch_assoc();
					
					$stmtCheckEvent->close();
					
					/*TE: We check to see if the event has already been closed or someone already suggested it be closed.
					 *If not we update the event with the suggestion.*/
					if((int)$resultRow["Deleted"]==1){
						/*TE: marking this as a success (1) since the event is closed.
						 *The application can then close the screen the user is on and direct them to the main page.
						 *Failures (0) don't do this and even though the event is closed, the user will be stuck on the same page.*/
						$response->wasSuccessful = 1;
						$response->message = "Event has already been closed";

					}else if((int)$resultRow["SuggestedToClose"]!=-1){
						/*TE: marking this as a success (1) since the user was beaten to suggesting it.
						 *The application can then close the screen the user is on and direct them to the map page.
						 *Failures (0) don't do direct the user to the map page etc.*/
						$response->wasSuccessful = 1;
						$response->message = "Event closure already suggested";
						
					}else{
						
						/*TE: Only need to update the suggested to close variable. If it is NULL, we return -1 in the LoadAllEvents.php script.
						 *The Application then uses this to check to make sure the Suggest to close button should be shown.*/
						$suggestCloseQuery = "UPDATE Event SET SuggestedToClose = ? WHERE Event_ID = ?";
					
						/*TE: Make sure we can create the prepared statement, bind the variables and exectute the statement.
						 *If an error happens the operation fails and the user is notified with an error*/
						if($stmtSuggestClose = $connection->prepare($suggestCloseQuery)){				
							if($stmtSuggestClose->bind_param("ii",$closer ,$eventId)){
								if($stmtSuggestClose->execute()){
									/*TE: The suggestion has been saved*/

									$stmtSuggestClose->close();

									//JW: obtains the event ogransiser's firebase id and sends them a push notification alerting
									//    them that their event can now be closed
									$fbIdQuery = "SELECT `Firebase_ID` FROM `User` WHERE `User_ID` = ?";

									if($stmtEventOrganiserFbId = $connection->prepare($fbIdQuery)){				
										if($stmtEventOrganiserFbId->bind_param("i", $organiser)){
											if($stmtEventOrganiserFbId->execute()){

												$result = $stmtEventOrganiserFbId->get_result();

												$row = $result->fetch_assoc();

												$stmtEventOrganiserFbId->close();

												$firebaseId = $row["Firebase_ID"];
												
												SendPushNotification($firebaseId, $eventName);
											}
										}
									}

									$response->wasSuccessful = 1;
									$response->message = "Event closure suggestion successfully saved";			
								}else{
									$response->wasSuccessful = 0;
									$response->message = "Could not execute statement to save suggestion.";	
								}
							}else{
								$response->wasSuccessful = 0;
								$response->message = "Could not bind parameters to statement to save suggestion.";	
							}
						}else{
							$response->wasSuccessful = 0;
							$response->message = "Could not prepare query to save suggestion.";	
						}
						/*TE: End of updated the suggester*/
						
					}
					/*TE: End of checking the event is closed or suggested to close.*/
					
				}else{
					$response->wasSuccessful = 0;
					$response->message = "Could not execute statement to check event.";	
				}
			}else{
				$response->wasSuccessful = 0;
				$response->message = "Could not bind parameters to statement to check event.";	
			}
		}else{
			$response->wasSuccessful = 0;
			$response->message = "Could not prepare statement to check event.";	
		}
	}else{
		$response->wasSuccessful = 0;
		$response->message = "Paremeters are missing from post message.";
	}
}else{
    $response->wasSuccessful = 0;
    $response->message = "Event not updated as post method was not used.";
}

$JSON = json_encode($response);
echo $JSON;

/**
 * This function sends a push notification to the creator of the event
 * alerting them that their event can now be closed.
 *
 * @param string $firebaseId
 * @param string $eventName
 * @return void
 */
function SendPushNotification($firebaseId, $eventName) {
	$url = "https://fcm.googleapis.com/fcm/send";
    $serverKey = "AAAAXdFzKcA:APA91bHA3jErNpIDywUf4fRLtY43XiVI_1INHIYfF7Jv_HuDaGPl9A8eMG_qtdui7sseOCmKtQbJt2zTVe3FJ69pBUnkssVwJQISDzSEVHkGMwKN6wGh3cRvbzqRtjm-r6rnjCxnobRl";
    
	$notificationTitle = "Your event may be ready to be closed";
	$notificationBody = "An attendee has suggested that your event: " . $eventName . ", may be ready for closure";

	$data = array (
		'to' => $firebaseId,
		'priority' => 'high',
		'notification'=> array(
			'title' => $notificationTitle,
			'body' => $notificationBody,
			'sound' => 'default',
			'icon' => 'push_icon',
		)
    );
    
    $json_message = json_encode($data);

    //JW: open connection using curl
    $ch = curl_init();

    //JW: set url, post method, post fields and http headers
    curl_setopt($ch, CURLOPT_URL, $url); //JW: sets url
	curl_setopt($ch, CURLOPT_POST, true); //JW: sets post method
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //JW: stores firebase result json reply as string instead of directly outputting json via curl_exec (which broke xamarin app as it was freaking out with multiple json replies to deal with)
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_message); //JW: sets json message
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: key=' . $serverKey
	]); //JW: set the content type and the firebase authorization key for our server in the http header
	

    //JW: execute post via curl
    $result = curl_exec($ch);
}

?>