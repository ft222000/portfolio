<?php
/**
 * Updates the password that is used for a given user.
 *
 * Accepts POST requests only.
 *
 * @param Password The current password of the user.
 * @param newPassword The new password to be used by this user.
 * @param id The id of the user for which the password should be updated.
 */

require_once("../util/Dbconnection.php");

$response = new stdClass();

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $password = mysqli_real_escape_string($connection,$_POST['Password']);
    $newPassword = mysqli_real_escape_string($connection,$_POST['newPassword']);
    $id = mysqli_real_escape_string($connection,$_POST['id']);
	/*TE: Select existing users password and salt from the database to compare to password provided. The orignal query had errors.*/	
    $query = "SELECT Password, Salt From User WHERE User_ID = '$id'";
	
    $result = $connection->query($query);
	/*TE: If the query was successful, we then proceed to analyse the details.*/
	if($result){
		/*TE: We check to ensure we actually have user details*/
		if($result->num_rows > 0){
			$row = $result->fetch_assoc();

			$dbPassword = $row['Password'];
			$dbSalt = $row['Salt'];
			/*TE: We hash the current password sent from the application (password) to see if it matches the password hash stored in the database.*/
			$hashedPassword = hash('sha256', $dbSalt.$password);
			/*TE: We check to make sure the two hashes match.*/
			if(strcasecmp($hashedPassword, $dbPassword) == 0){
						   
				/*TE: Since the user entered their correct password, when then hash the new password to store in the database. This was missed originally*/
				$newHashedPassword = hash('sha256', $dbSalt.$newPassword);
				
				/*TE: We update the password in the database. The original query had some issues. I'd avoid using '' around table and field names.*/
				$updateQuery = "Update User SET Password = '$newHashedPassword' WHERE User_ID = '$id'";
				
				$updateResult = $connection->query($updateQuery);
				
				/*TE: We check to make sure the password query DID run. We don't want the user to think it worked when the query actually failed to run*/
				if($updateResult){
					$response->wasSuccessful = 1;
					$response->serverMessage = "Password Successfully Changed";
				}else{
					$response->wasSuccessful = 0;
					$response->serverMessage = "Error Updating Password";
				}
				echo json_encode($response);			
			}
			else{
				$response->wasSuccessful = 0;
				$response->serverMessage = "Current Password is incorrect";
				echo json_encode($response);
			}
		}
		else{
			$response->wasSuccessful = 0;
			$response->serverMessage = "No matches in Database";
			echo json_encode($response);
		}
	}else{
		$response->wasSuccessful = 0;
		$response->serverMessage = "Error Retrieving details";
		echo json_encode($response);
	}
}
else{
    $response->wasSuccessful = 0;
    $response->serverMessage = "Method not attempted, POST method not used";
	echo json_encode($response);
}



?>