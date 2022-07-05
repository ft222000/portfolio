<?php
    /**
     * User: Jordan Woolley
     * Date: 28/08/18
     * Time: 02:00pm
     * 
     * Jordan: This api returns the notification preferences from the db so that toggle states can be set approporiately on login
     */

    require_once("../util/Dbconnection.php");

    $response = new stdClass();

    if($_SERVER["REQUEST_METHOD"]== "POST"){

        $userId = $_POST['id'];

        $query = "SELECT `Notification_ID` FROM `PreferredNotification` WHERE `User_ID` = '$userId'";
        
        $response = [];
        //JW: check if there were any results from the db
        if ($result = $connection->query($query)) {

            $i = 0;

            //JW: stores all the results
            while($row = $result->fetch_assoc()){
                $response[$i] = new stdClass();
                $response[$i]->Notification_ID = $row["Notification_ID"];
                $i++;
            }
            $result->free();
            
        } else {
            $response->wasSuccessful = 0;
            $response->message = "No notification types found -- send help!";
        }
    } else {
        $response->wasSuccessful = 0;
        $response->message = "Unable to fetch notification types from db, POST method not used";
    }
    echo json_encode($response);
?>