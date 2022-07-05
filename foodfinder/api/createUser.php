<?php
/**
 * Created by PhpStorm.
 * User: John Aslin
 * Date: 25/05/2018
 * Time: 8:49 PM
 *
 * Handles the creation of users for the Xamarin side of the application.
 *
 *
 * @param EmailAddress      The email address for the user being created
 * @param Password          The password for the user, which will be hashed before being stored in the database
 * @param PermissionLevel   The intended permission level of the user.
 * @param PrimaryCampus     The primary campus that the user would like for the application
 */

require_once("../util/Dbconnection.php");
require_once("../util/userCreation.php");

//Kim: Check to see what kind of request is being used.
$method = $_SERVER['REQUEST_METHOD'];

//Kim: Object to hold response information
$response = new stdClass();

if($method == "POST")
{
	/*TE: Values to be saved for the new user.*/
    $email = $_POST['EmailAddress'];
	$password = $_POST['Password'];
	$permissionLevel = $_POST['PermissionLevel'];
	$primaryCampus = $_POST['PrimaryCampus'];
    
	/*JW: Search db for email address
	 *TE: This now uses a prepared statement to retrieve a user id for the submitted email.
	 *If the user id exists for the email we do not create the user.
	 *If the prepared statement fails to be created, bound or executed, we return an error message to the application.
	 */	
	if($checkEmailAddressStatement = $connection->prepare("SELECT `User_ID`, `isDeleted` FROM `User` WHERE `EmailAddress` = ?")){
		if($checkEmailAddressStatement->bind_param("s",$email)){
			if($checkEmailAddressStatement->execute()){		
			
				/*TE: Get the result to check the number of rows.*/
				$emailResult = $checkEmailAddressStatement->get_result();
				
				/*TE: Close the statement otherwise it will stop the user creation prepared statement from being created.*/
				$checkEmailAddressStatement->close();				

				/*TE: We check to see if there is an existing entry in the database for the user. Do not create user if there is.*/
				if (mysqli_num_rows($emailResult) > 0) {

					$row = $emailResult->fetch_assoc();
					$userID = $row['User_ID'];
					$wasDeleted = $row['isDeleted'];
                    $salt = GetSalt();
                    $fPassword = hash('sha256', $salt.$password);

                    /**
                     * JW: The mobile application sends the campus names through to the
                     *     api instead of the id. This if statement checks if the primaryCampus
                     * 	   variable is not numeric, and if it isn't, converts the name to the id
                     */
                    if ($wasDeleted == 1) {
                        if (!is_numeric($primaryCampus)) {
                            if ($campusSwitchToIdStatement = $connection->prepare("SELECT `Campus_ID` FROM `Campus` WHERE `Name` = ?")) {
                                if ($campusSwitchToIdStatement->bind_param("s", $primaryCampus)) {
                                    if ($campusSwitchToIdStatement->execute()) {

                                        //JW: grab the results and place the id back into primaryCampus, overwriting the name there previously
                                        $result = $campusSwitchToIdStatement->get_result();

                                        $row = $result->fetch_assoc();

                                        $primaryCampus = $row['Campus_ID'];

                                        /*TE: Close the statement all neat and tidy.*/
                                        $campusSwitchToIdStatement->close();
                                    }
                                }
                            }
                        }

                        if ($newUserStatement = $connection->prepare("UPDATE `User` SET `Password` = ?, `Salt` = ?, `RequestedOrganiserPrivileges` = '0', `RequestReason` = NULL, `IsDeleted` = '0', `Activated` = '0', `EOApprover` = NULL, `HasPermissionLevel` = ?, `PrimaryCampus` = ? WHERE User_ID = ?")) {
                            if ($newUserStatement->bind_param("ssiii", $fPassword, $salt, $permissionLevel, $primaryCampus, $userID)) {
                                if ($newUserStatement->execute()) {

                                    /*TE: Close the statement all neat and tidy.*/
                                    $newUserStatement->close();

                                    /*TE: The only time we send a succussful response.*/
                                    $response->wasSuccessful = 1;
                                    $response->message = "User has been created successfully!";

                                } else {
                                    $response->wasSuccessful = 0;
                                    $response->message = "Creating user statement has failed to execute with error: " . $newUserStatement->error;
                                }
                            } else {
                                $response->wasSuccessful = 0;
                                $response->message = "Binding parameters to the user creation statement has failed wwith error: " . $newUserStatement->error;
                            }
                        } else {
                            $response->wasSuccessful = 0;
                            $response->message = "Preparing user creation statement has failed with error: " . $newUserStatement->error;
                        }
                    } else {
                        $response->wasSuccessful = 0;
                        $response->message = "This user already exists, try the 'Forgot My Password' option instead.";
                    }

				//JW: if there are no matches, insert the user into the db and return a success message.	
				} else {					
					/*TE: Get the salt to create the hashed password. The function exists in Joeby's userCreation.php file in utils.*/		
					$salt = GetSalt();
					$fPassword = hash('sha256', $salt.$password);

					/**
					 * JW: The mobile application sends the campus names through to the
					 *     api instead of the id. This if statement checks if the primaryCampus
					 * 	   variable is not numeric, and if it isn't, converts the name to the id
					 */
					if (!is_numeric($primaryCampus)) {
						if($campusSwitchToIdStatement = $connection->prepare("SELECT `Campus_ID` FROM `Campus` WHERE `Name` = ?")) {
							if($campusSwitchToIdStatement->bind_param("s", $primaryCampus)){
								if($campusSwitchToIdStatement->execute()){

									//JW: grab the results and place the id back into primaryCampus, overwriting the name there previously
									$result = $campusSwitchToIdStatement->get_result();

									$row = $result->fetch_assoc();

									$primaryCampus = $row['Campus_ID'];

									/*TE: Close the statement all neat and tidy.*/
									$campusSwitchToIdStatement->close();
								}
							}
						}
					}

					/*TE: Create the new user prepared statement to insert the new user into the database.
					 *If the prepared statement fails to be created, bound or executed, we return an error message to the application.
					 */
					if($newUserStatement = $connection->prepare("INSERT INTO `User` (`EmailAddress`, `Password`, `Salt`, `RequestedOrganiserPrivileges`, `RequestReason`, `IsDeleted`, `Activated`, `EOApprover`, `HasPermissionLevel`, `PrimaryCampus`) VALUES (?, ?, ?, '0', NULL, '0', '0',NULL, ?, ?)")) {
						if($newUserStatement->bind_param("sssii", $email, $fPassword, $salt, $permissionLevel, $primaryCampus)){
							if($newUserStatement->execute()){
								
								/*TE: Close the statement all neat and tidy.*/
								$newUserStatement->close();
								
								/*TE: The only time we send a succussful response.*/
								$response->wasSuccessful = 1;
								$response->message = "User has been created successfully!";
								
							}else{
								$response->wasSuccessful = 0;
								$response->message = "Creating user statement has failed to execute with error: ".$newUserStatement->error;
							}
						}else{
							$response->wasSuccessful = 0;
							$response->message = "Binding parameters to the user creation statement has failed wwith error: ".$newUserStatement->error;
						} 					
					}else{
						$response->wasSuccessful = 0;
						$response->message = "Preparing user creation statement has failed with error: ".$newUserStatement->error;
					}
				}
				/*TE: End of user creation logic.*/				
			}else{
				$response->wasSuccessful = 0;
				$response->message = "Executing checking email statement has failed to execute with error: ".$checkEmailAddressStatement->error;
			}
		}else{
			$response->wasSuccessful = 0;
			$response->message = "Binding parameters to the email checking statement has failed to execute with error: ".$checkEmailAddressStatement->error;
		}
	}else{
		$response->wasSuccessful = 0;
		$response->message = "Preparing user creation statement has failed with error: ".$checkEmailAddressStatement->error;
	}
	/*TE: End of the existing email checking logic.*/	
} else {
    // Kim: Post method not used
    $response->wasSuccessful = 0;
    $response->message = "Post method is not used";
}
$JSON = json_encode($response);
echo $JSON;