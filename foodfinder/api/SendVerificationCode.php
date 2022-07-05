<?php
    /**
     * Checks the given verification code, and updates the activated status of a user as required.
     *
     * Accepts POST requests only.
     *
     * @param Email The email address of the account being activated.
     * @param VerificationCode The verification code that user is attempting to provide.
     */
   
    require_once("../util/Dbconnection.php");
    //Kim: Object to hold response information
    $response = new stdClass();

    if($_SERVER["REQUEST_METHOD"]== "POST"){
        $email = $_POST['Email'];
        $code = $_POST['VerificationCode'];
        $activated = 1;
        //Kim: Select the user's salt 
        $selectQuery ="SELECT `Salt` FROM `User` WHERE `EmailAddress` = ?";
        $stmt = $connection->prepare($selectQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        //Kim: Get result
        $result = $stmt->get_result();        
        if ($result->num_rows > 0) 
        {
            $row = $result->fetch_assoc();
            $salt = $row['Salt'];
            //Kim: Select first five characters as verification code
            $salt = substr($salt,0,5);
            
            if($code == $salt)
            {
                
                // Kim: Update Activated value, if the code matches
                $updateQuery = "UPDATE `User` SET `Activated` = ? WHERE `EmailAddress` = ?";
                $stmt = $connection->prepare($updateQuery);
                $stmt->bind_param("is",$activated, $email);
                $stmt->execute();     
                $response->wasSuccessful = 1;
                $response->message = "Verification code has successfully verified";           
            }
            else
            {
                $response->wasSuccessful = 0;
                $response->message = "Verification code not match";
            }
        }
        else
        {
            $response->wasSuccessful = 0;
            $response->message = "Unable to find salt";
        }
    }
    else
    {
        $response->wasSuccessful = 0;
        $response->message = "Unable to use POST";
    
    }
    echo json_encode($response);
    ?>