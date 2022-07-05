<?php
/** *
 * This script recieves a http request from the application and then saves the users feedback regarding the mobile application in the database.
 *
 * Accepts POST requests only.
 *
 *@PARAM UserId The user send the feedback.
 *@PARAM Feedback The feedback submitted by the user.
 *
 */
require_once("../util/Dbconnection.php");

$method = $_SERVER['REQUEST_METHOD'];

$response = new stdClass();

if($method == "POST") {
	
	/*TE: Make sure variable is set. Don't want ot update it with an empty field*/
	if(isset($_POST["Feedback"]) && isset($_POST["UserId"])){
		
		$feedback = $_POST["Feedback"];
		$userId = $_POST["UserId"];
		
		$saveFeedbackquery = "INSERT INTO Feedback(`User_ID`,`FeedbackMessage`) VALUES(?,?)";	
		
		/*TE: make sure we can create the prepared statement, bind the variable and exectute it.
		 *If an error happens the operation fails and the user is notified with an error
		 */
		if($stmtSaveFeedback = $connection->prepare($saveFeedbackquery)){				
			if($stmtSaveFeedback->bind_param("is",$userId,$feedback)){
				if($stmtSaveFeedback->execute()){
					/*TE: Event has been closed*/									
					$response->wasSuccessful = 1;
					$response->message = "Feedback saved successfully";					
				}else{
					$response->wasSuccessful = 0;
					$response->message = "Could not execute statement to save feedback.";
				}
			}else{
				$response->wasSuccessful = 0;
				$response->message = "Could not bind parameters to statement to save feedback.";	
			}
		}else{
			$response->wasSuccessful = 0;
			$response->message = "Could not prepare query to save feedback.";	
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