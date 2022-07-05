<?php
    /**
     * This API retrieves a users primary campus and coordinates.
     *
     * Accepts POST requests only.
     *
     * @param id The unique identification number of the user.
     */

    require_once("../util/Dbconnection.php");

    $response = new stdClass();

    if($_SERVER["REQUEST_METHOD"]== "POST"){

        $userId = $_POST['id'];

        //JW: find primary campus id
        $primaryCampusQuery = "SELECT `PrimaryCampus` FROM `User` WHERE `User_ID` = ?";

        if($primaryCampusStatement = $connection->prepare($primaryCampusQuery)) {
            if($primaryCampusStatement->bind_param("i", $userId)){
                if($primaryCampusStatement->execute()){

                    $result = $primaryCampusStatement->get_result();
                    $row = $result->fetch_assoc();

                    $primaryCampusID = $row['PrimaryCampus'];

                    //JW: close the statement
                    $primaryCampusStatement->close();

                    //JW: convert primary campus ID to a name, and obtain the coordinates
                    $primaryIdToNameQuery = "SELECT `Name`, `Longitude`, `Latitude` FROM `Campus` WHERE `Campus_ID` = ?";

                    if($primaryIdToNameStatement = $connection->prepare($primaryIdToNameQuery)) {
                        if($primaryIdToNameStatement->bind_param("i", $primaryCampusID)){
                            if($primaryIdToNameStatement->execute()){

                                $result = $primaryIdToNameStatement->get_result();
                                $row = $result->fetch_assoc();

                                $primaryCampus = $row['Name'];
                                $longitude = $row['Longitude'];
                                $latitude = $row['Latitude'];

                                //JW: close the statement
                                $primaryIdToNameStatement->close();

                                $response->wasSuccessful = 1;
                                $response->message = "primary campus data retrieved";
                                $response->primaryCampus = $primaryCampus;
                                $response->longitude = $longitude;
                                $response->latitude = $latitude;
                            }else{
                                $response->wasSuccessful = 0;
                                $response->message = "primaryIdToNameStatement failed to execute";
                            }
                        }else{
                            $response->wasSuccessful = 0;
                            $response->message = "primaryIdToNameStatement failed to bind parameters";
                        }
                    }else{
                        $response->wasSuccessful = 0;
                        $response->message = "primaryIdToNameStatement failed to prepare the statement";
                    }
                }else{
                    $response->wasSuccessful = 0;
                    $response->message = "primaryCampusStatement failed to execute";
                }
            }else{
                $response->wasSuccessful = 0;
                $response->message = "primaryCampusStatement failed to bind parameters";
            }
        }else{
            $response->wasSuccessful = 0;
            $response->message = "primaryCampusStatement failed to prepare the statement";
        }
    } else {
        $response->wasSuccessful = 0;
        $response->message = "Unable to retrieve primary campus data from db, POST method not used";
    }

    //JW: send response to application via json
    echo json_encode($response);
?>