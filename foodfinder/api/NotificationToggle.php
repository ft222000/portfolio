<?php
/**
 * User: Kim
 * Date: 15/08/18
 * Time: 10.00 AM
 * 
 * Kim: This php saves preferred notification type into database
 */

require_once("../util/Dbconnection.php");


if($_SERVER["REQUEST_METHOD"]== "POST"){
 
    $userID = $_POST["User_ID"];
    $pushSwitch = $_POST["PushSwitch"];
    $emailSwitch = $_POST["EmailSwitch"];
   
    //Kim & JW: all these settings are to be hardcoded in the db notification type table
    if($emailSwitch == "True")
    {
        $emailSwitch = 1;
    }
    else if ($emailSwitch == "False")
    {
        $emailSwitch = 2;
    }

    if($pushSwitch == "True")
    {
        $pushSwitch = 3;
    }
    else if ($pushSwitch == "False")
    {
        $pushSwitch = 4;
    }

    // Kim: Delete all notification data for the user
    $deleteQuery = "DELETE FROM `PreferredNotification` WHERE `User_ID` = '$userID'";
    mysqli_query($connection, $deleteQuery);

    // JW: insert desired email setting into db
    $insertEmailQuery = "INSERT INTO `PreferredNotification` (`User_ID`, `Notification_ID`) VALUES ('$userID','$emailSwitch')";
    mysqli_query($connection, $insertEmailQuery);
    
    // JW: insert desired push setting into db
    $insertPushQuery = "INSERT INTO `PreferredNotification` (`User_ID`, `Notification_ID`) VALUES ('$userID','$pushSwitch')";
    mysqli_query($connection, $insertPushQuery);
}
?>