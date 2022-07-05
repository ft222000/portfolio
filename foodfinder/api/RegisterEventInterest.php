<?php
/**
 * Notepad++
 * User: Thomas Enniss
 * Date: 11/09/2018 
 * Time: 9:20pm
 */

require_once("../util/Dbconnection.php");

$method = $_SERVER['REQUEST_METHOD'];

$response = new stdClass();

if($method == "POST") {	
	/*TE: Make sure the event and user id where sent.*/
	if(isset($_POST["eventId"])&& isset($_POST["userId"])){
		
		$eventRegisteringInterestIn = $_POST["eventId"];
		$userRegisteringInterest = $_POST["userId"];
		
		/*TE: make sure the event and user id are not empty strings or no values.*/
		if($eventRegisteringInterestIn!="" && $userRegisteringInterest!=""){
		
			$checkAttendenceExistsQuery = "SELECT Attends_ID FROM Attends WHERE Event_ID = ? AND User_ID = ?";
			
			/*TE: We first check to make sure the user has not registered interest in this event previously.*/
			if($stmtCheckAttendance = $connection->prepare($checkAttendenceExistsQuery)){				
				if($stmtCheckAttendance->bind_param("ii",$eventRegisteringInterestIn,$userRegisteringInterest)){
					if($stmtCheckAttendance->execute()){
						/*TE: Grab the result from the query.*/
						$checkAttendanceResult = $stmtCheckAttendance->get_result();
						$stmtCheckAttendance->close();
						
						/*TE: If there are no lines, the user has not registered (viewed) this event before*/
						if (mysqli_num_rows($checkAttendanceResult) > 0) {
							$response->wasSuccessful = 0;
							$response->message = "User already interested in this event";
						}else{
							/*TE: We attempt to save the event id and the user to the database.*/
							$attendanceQuery = "INSERT INTO `Attends`(`Event_ID`, `User_ID`) VALUES (?, ?)";
							
							if($stmtAttends = $connection->prepare($attendanceQuery)){
								if($stmtAttends->bind_param("ii",$eventRegisteringInterestIn, $userRegisteringInterest)){
									if($stmtAttends->execute()){								
										$response->wasSuccessful = 1;
										$response->message = "Interest has successfully been registered!";									
									}else{
										$response->wasSuccessful = 0;
										$response->message = "Could not execute statement to register interest.";	
									}									
									/*TE: Make sure statement is definitely closed by placing it after the above branching statements.*/
									$stmt->close();
									
								}else{
									$response->wasSuccessful = 0;
									$response->message = "Could not bind parameters to statement to register interest.";	
								}
							}else{
								$response->wasSuccessful = 0;
								$response->message = "Could not prepare query to register interest.";	
							}						
						}				
					}else{
						$response->wasSuccessful = 0;
						$response->message = "Could not execute statement to check previously registered interest.";	
					}
				}else{
					$response->wasSuccessful = 0;
					$response->message = "Could not bind parameters to statement to check previously registered interest.";	
				}
			}else{
				$response->wasSuccessful = 0;
				$response->message = "Could not prepare query to check previously registered interest.";	
			}			
		}else{
			$response->wasSuccessful = 0;
			$response->message = "EventId and / or UserId variables are empty.";
		}
	}else{
		$response->wasSuccessful = 0;
		$response->message = "EventId and / or UserId variables not set.";	
	}
} else {
    $response->wasSuccessful = 0;
    $response->message = "Interest has not been registered as post method was not used.";
}

$JSON = json_encode($response);
echo $JSON;