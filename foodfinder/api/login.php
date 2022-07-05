<?php
/**
 * JW: API for logging user into the mobile application
 *     at the moment
 */

require_once("../util/Dbconnection.php");

$response = new stdClass();

if($_SERVER["REQUEST_METHOD"] == "POST"){

    //JW: store email and password send from application post request
    $email = $_POST['EmailAddress'];
	$password = $_POST['Password'];
	$firebaseId = $_POST['FirebaseID'];

    $accountQuery = "SELECT `EmailAddress`,	`Password`,`Salt`,`User_ID`,`HasPermissionLevel`,`Activated`, User.IsDeleted,`Name`, Campus.Longitude, Campus.Latitude 
						FROM `User` 
						INNER JOIN Campus 
						ON Campus.Campus_ID = User.PrimaryCampus 
						WHERE `EmailAddress` = ?";

	if($loginStatement = $connection->prepare($accountQuery)) {
		if($loginStatement->bind_param("s", $email)){
			if($loginStatement->execute()){
				
				$result = $loginStatement->get_result();

				$row = $result->fetch_assoc();

				/*TE: Close the statement all neat and tidy.*/
				$loginStatement->close();

				$id = $row['User_ID'];
				$permissionLevel = $row['HasPermissionLevel'];
				$activated = $row['Activated'];
				$isDeleted = $row['IsDeleted'];
				$primaryCampus = $row['Name'];
				$campusLongitude = $row['Longitude'];
				$campusLatitude = $row['Latitude'];
				$dbPassword = $row['Password'];
				$dbSalt = $row['Salt'];
				$hashedPassword = hash('sha256', $dbSalt.$password);
				
				if (strcasecmp($hashedPassword, $dbPassword) == 0) {
				
					//update firebase id on login
					$fbQuery = "UPDATE `User` SET `Firebase_ID` = ? WHERE `User_ID` = ?";
	
					if ($updateFbIdStatement = $connection->prepare($fbQuery)) {
						if ($updateFbIdStatement->bind_param("si", $firebaseId, $id)) {
							if ($updateFbIdStatement->execute()) {
								$updateFbIdStatement->close();
	
								$response->wasSuccessful = 1;
								$response->message = "Login was successful, firebase id saved to db";
								$response->id = $id;
								$response->permission = $permissionLevel;
								$response->activated = $activated;
								$response->isDeleted = $isDeleted;
								$response->primaryCampus = $primaryCampus;
								$response->campusLongitude = $campusLongitude;
								$response->campusLatitude = $campusLatitude;
							}else{
								$response->wasSuccessful = 0;
								$response->message = "Firebase update statement has failed to execute";
							}
						}else{
							$response->wasSuccessful = 0;
							$response->message = "Binding parameters to the firebase update statement has failed";
						}
					}else{
						$response->wasSuccessful = 0;
						$response->message = "Preparing firebase update statement has failed";
					}
				} else{
					$response->wasSuccessful = 0;
					$response->message = "Incorrect Email address or Password";
				}	
			}else{
				$response->wasSuccessful = 0;
				$response->message = "Login statement has failed to execute";
			}
		}else{
			$response->wasSuccessful = 0;
			$response->message = "Binding parameters to the login statement has failed";
		} 					
	}else{
		$response->wasSuccessful = 0;
		$response->message = "Preparing login statement has failed";
	}
} else {
    $response->wasSuccessful = 0;
    $response->message = "Login not attempted, POST method not used";
}

//JW: send response to application via json
echo json_encode($response);
?>