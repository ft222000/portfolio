<?php
    /**
     * Retrieves a list of campuses that a given user has subscribed to.
     *
     * Accepts POST requests only.
     *
     * @param id The id of the user that you want information about.
     */

    require_once("../util/Dbconnection.php");

    $response = new stdClass();

    if($_SERVER["REQUEST_METHOD"]== "POST"){

        $userId = $_POST['id'];
        $response = [];
        $i = 0;

        //JW: find primary campus id
        $primaryCampusQuery = "SELECT `PrimaryCampus` FROM `User` WHERE `User_ID` = '$userId'";
        $primaryResult = mysqli_query($connection, $primaryCampusQuery);

        //JW: convert primary campus id to name
        if ($primaryResult->num_rows > 0) {
            $row = $primaryResult->fetch_assoc();
            $primaryCampusID = $row['PrimaryCampus'];

            $primaryIdToNameQuery = "SELECT `Name` FROM `Campus` WHERE `Campus_ID` = '$primaryCampusID'";
            $primaryNameResult = mysqli_query($connection, $primaryIdToNameQuery);

            if ($primaryNameResult->num_rows > 0) { 
                $row = $primaryNameResult->fetch_assoc();
                
                //JW: add result to array
                $subscribedCampuses[] = $row['Name'];
            }
        }

        //JW: find secondary campuse ids
        $secondaryCampusQuery = "SELECT `Campus_ID` FROM `SubscribedTo` WHERE `User_ID` = '$userId'";
        $secondaryResult = mysqli_query($connection, $secondaryCampusQuery);

        //JW: convert secondary campus ids to name
        if ($secondaryResult->num_rows > 0) {
            while ($row = $secondaryResult->fetch_assoc()) {
                $secondaryCampusID = $row['Campus_ID'];

                $secondaryIdToNameQuery = "SELECT `Name` FROM `Campus` WHERE `Campus_ID` = '$secondaryCampusID'";
                $secondaryNameResult = mysqli_query($connection, $secondaryIdToNameQuery);

                if ($secondaryNameResult->num_rows > 0) { 
                    $row = $secondaryNameResult->fetch_assoc();
                    
                    //JW: add result to array
                    $subscribedCampuses[] = $row['Name'];
                }
            }
        }
        
        foreach($subscribedCampuses as $campus) {
            $response[$i]->Name = $campus;
            $i++;
        }
    } else {
        $response[$i]->wasSuccessful = 0;
        $response[$i]->message = "Unable to retrieve subscribed campuses from db, POST method not used";
    }

    //JW: send response to application via json
    echo json_encode($response);
?>