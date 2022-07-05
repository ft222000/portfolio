<?php
/**
 * Notepad++
 * User: Thomas Enniss
 * Date: 15/09/2018 
 * Time: 1:00pm
 */

require_once("../util/Dbconnection.php");

$method = $_SERVER['REQUEST_METHOD'];

$response = new stdClass();

if($method == "POST") {
	
	/*NEED TO CHECK IF EVENT NOT CLOSED*/
	
	/*TE: Make sure variable is set. Don't want ot update it with an empty field*/
	if(isset($_POST["EventId"])){
		
		$eventId = $_POST["EventId"];
		
		$checkEventClosed = "SELECT Deleted FROM Event WHERE Event_ID = ?";
		
		/*TE: Check if event already closed or not etc. Capture any errors.*/
		if($stmtCheckEvent = $connection->prepare($checkEventClosed)){				
			if($stmtCheckEvent->bind_param("i",$eventId)){
				if($stmtCheckEvent->execute()){
					
					$checkEventResults = $stmtCheckEvent->get_result();
					$resultRow = $checkEventResults->fetch_assoc();
					
					$stmtCheckEvent->close();
					
					/*TE: We check to see if the event has already been closed, otherwise we close it.*/
					if((int)$resultRow["Deleted"]==1){
						/*TE: marking this as a success (1) since the event is closed.
						 *The application can then close the screen the user is on and direct them to the main page.
						 *Failures (0) don't do this and even though the event is closed, the user will be stuck on the same page.*/
						$response->wasSuccessful = 1;
						$response->message = "Event has already been closed";					
						
					}else{
						/*TE: Soft delete event to close it. Could have used event delete script but did not want to break a web portal script.*/
						$closeEventQuery = "UPDATE Event SET Deleted = 1 WHERE Event_ID = ?";	
						
						/*TE: make sure we can create the prepared statement, bind the variable and exectute it.
						 *If an error happens the operation fails and the user is notified with an error*/
						if($stmtCloseEvent = $connection->prepare($closeEventQuery)){				
							if($stmtCloseEvent->bind_param("i",$eventId)){
								if($stmtCloseEvent->execute()){
									/*TE: Event has been closed*/									
									$response->wasSuccessful = 1;
									$response->message = "Event Closed Successfully";					
								}else{
									$response->wasSuccessful = 0;
									$response->message = "Could not execute statement to close event.";
								}
							}else{
								$response->wasSuccessful = 0;
								$response->message = "Could not bind parameters to statement to close event.";	
							}
						}else{
							$response->wasSuccessful = 0;
							$response->message = "Could not prepare query to close event.";	
						}
					}
					/*TE: End of checking the event is closed*/
				
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
} else {
    $response->wasSuccessful = 0;
    $response->message = "Event not updated as post method was not used.";
}

$JSON = json_encode($response);
echo $JSON;
?>