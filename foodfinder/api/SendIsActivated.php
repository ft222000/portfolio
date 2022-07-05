<?php
    /**
     * Retrieves the activated state of a user based on their email address.
     *
     * Accepts POST requests only.
     *
     * @param Email The email address of the user being queried.
     */
   
    require_once("../util/Dbconnection.php");
    $response = new stdClass();
    // Kim: Gets the type of request received
    if($_SERVER["REQUEST_METHOD"] == "POST"){

        $email = $_POST['Email'];
         
        //Kim: Select Activated according to the user email.
        $selectQuery ="SELECT `Activated` FROM `User` WHERE `EmailAddress` = ?";
        $stmt = $connection->prepare($selectQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        //Kim: Get result
        $result = $stmt->get_result(); 
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            
            $activate = $row["Activated"];
            $response->Activated = $activate;
            $response->wasSuccessful = 1;
            $response->message = "Activated data found"; 
             
        }
        else
        {
            $response->wasSuccessful = 0;
            $response->message = "No Activated data found";           
        }

    }
    else
    {
        $response->wasSuccessful = 0;
        $response->message = "POST is not used";            
    }
    echo json_encode($response);
?>