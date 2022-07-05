<?php
    /**
     * Updates the primary campus of a given user.
     *
     * Accepts POST requests only.
     *
     * @param PrimaryCampus The name of the campus that should be added as the primary campus.
     * @param id The ID of the user that the campus should be added to
     */

    require_once("../util/Dbconnection.php");

    $response = new stdClass();

    if($_SERVER["REQUEST_METHOD"]== "POST"){

        $primaryCampus = $_POST['PrimaryCampus'];
        $id = $_POST['id'];
        
        //Kim & Jordan: Select Campus_ID, Long and Lat according to the primary campus name.
        $selectQuery ="SELECT `Campus_ID`, `Longitude`, `Latitude` FROM `Campus` WHERE `Name` = '$primaryCampus'";
        $primaryCampusID_result = mysqli_query($connection, $selectQuery);
            
        if ($primaryCampusID_result->num_rows > 0) {
            $row = $primaryCampusID_result->fetch_assoc();
            $primaryCampusID = $row['Campus_ID'];
            $primaryCampusLongitude = $row['Longitude'];
            $primaryCampusLatitude = $row['Latitude'];
        }
        
        // Kim: Update primary campus 
        $updateQuery = "UPDATE `User` SET `PrimaryCampus` = '$primaryCampusID' WHERE `User_ID` = '$id'";
        mysqli_query($connection, $updateQuery);

        $response->wasSuccessful = 1;
        $response->message = "Primary campus saved to db -- returning primary campus coordinates";
        $response->primaryCampusLongitude = $primaryCampusLongitude;
        $response->primaryCampusLatitude = $primaryCampusLatitude; 
    } else {
        $response->wasSuccessful = 0;
        $response->message = "Unable to upload primary campus to db, POST method not used";
    }

    //JW: send response to application via json
    echo json_encode($response);
?>