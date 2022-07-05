<?php
    /**
     * Retrieves a list of all campuses and their associated ID
     *
     * Accepts GET requests only.
     */

    require_once("../util/Dbconnection.php");

    $response = new stdClass();

    if($_SERVER["REQUEST_METHOD"] == "GET"){

        $query ="SELECT `Name`,`Campus_ID` FROM `Campus` WHERE `isDeleted` = 0";

        $response = [];
        //JW: check if there were any results from the db
        if ($result = $connection->query($query)) {

            $i = 0;

            //JW: stores all the results into tuples
            while($row = $result->fetch_assoc()){
                $response[$i] = new stdClass();
                $response[$i]->Name = $row["Name"];
                $response[$i]->Id = $row["Campus_ID"];
                $i++;
            }
            $result->free();
            
        } else {
            $response->wasSuccessful = 0;
            $response->message = "No campuses found in the db -- send help!";
        }
    } else {
        $response->wasSuccessful = 0;
        $response->message = "Unable to fetch campuses from db, GET method not used";
    }
    echo json_encode($response);
?>