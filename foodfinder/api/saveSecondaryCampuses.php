<?php
    /**
     * Updates the secondary campuses of a given user.
     *
     * Accepts POST requests only.
     *
     * @param id The id of the user to have its secondary campuses updated.
     * @param NumOfSecondaryCampuses The number of secondary campuses to be added.
     * @param SecondaryCampusX The name of the campuses to be added. (Replace X with numbers 0 to TagCount)
     */

    require_once("../util/Dbconnection.php");

    $response = new stdClass();

    if($_SERVER["REQUEST_METHOD"]== "POST"){

        $id = $_POST['id'];
        $numOfSecondaryCampuses = $_POST['NumOfSecondaryCampuses'];

        //JW: loop through each secondary campus stored in dictionary; query the db for its campus id; store campus id in array
        for ($i = 0; $i < $numOfSecondaryCampuses; $i++)
        {
            $secondaryCampus = $_POST['SecondaryCampus' . $i];

            $selectSecQuery = "SELECT `Campus_ID` FROM `Campus` WHERE `Name` = '$secondaryCampus'";
            $secondaryCampusID_result = mysqli_query($connection, $selectSecQuery);

            if ($secondaryCampusID_result->num_rows > 0) {
                $row = $secondaryCampusID_result->fetch_assoc();

                $secondaryCampusID[] = $row['Campus_ID'];
            }
        }
        
        // Kim: Update primary campus 
        $updateQuery = "UPDATE `User` SET `PrimaryCampus` = '$primaryCampusID' WHERE `User_ID` = '$id'";
        mysqli_query($connection, $updateQuery);

        // Kim: Delete all subscribeto data for the user
        $deleteQuery = "DELETE FROM `SubscribedTo` WHERE `User_ID` = '$id'";
        mysqli_query($connection, $deleteQuery);

        // Kim & JW: loop over secondary campus id array and insert them into the subscribeto table
        foreach($secondaryCampusID as $value)
        {
            $insertQuery = "INSERT INTO `SubscribedTo` (`User_ID`, `Campus_ID`) VALUES ('$id','$value')";
            mysqli_query($connection, $insertQuery);
        }

        $response->wasSuccessful = 1;
        $response->message = "Secondary campuses saved to db";
    } else {
        $response->wasSuccessful = 0;
        $response->message = "Unable to upload campuses to db, POST method not used";
    }

    //JW: send response to application via json
    echo json_encode($response);
?>