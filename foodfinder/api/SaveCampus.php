<?php
    /**
     * This API updates a users primary and secondary campuses, and returns the primary campus coordinates.
     *
     * Accepts POST requests only.
     *
     * @param PrimaryCampus The name of the primary campus the user has selected.
     * @param id The unique identification number of the user.
     * @param SecondaryCampus The names of the secondary campuses the user has selected. 
     *                        For each secondary campus, a number will be appended to this 
     *                        parameter, starting from 0 (SecondaryCampus0), and adding 1
     *                        for each additional secondary campus (SecondaryCampus1, SecondaryCampus2, etc).
     * @param NumOfSecondaryCampuses The number of secondary campuses that have been selected.
     */
    require_once("../util/Dbconnection.php");
    $response = new stdClass();
    if($_SERVER["REQUEST_METHOD"]== "POST"){

        $primaryCampus = $_POST['PrimaryCampus'];
        $id = $_POST['id'];
        $numOfSecondaryCampuses = $_POST['NumOfSecondaryCampuses'];

        //Kim & Jordan: Select Campus_ID, Long and Lat according to the primary campus name.
        $selectQuery ="SELECT `Campus_ID`, `Longitude`, `Latitude` FROM `Campus` WHERE `Name` = ?";

        if($primaryCampusDataStatement = $connection->prepare($selectQuery)) {
            if($primaryCampusDataStatement->bind_param("s", $primaryCampus)){
                if($primaryCampusDataStatement->execute()){

                    $result = $primaryCampusDataStatement->get_result();
                    $row = $result->fetch_assoc();

                    $primaryCampusID = $row['Campus_ID'];
                    $primaryCampusLongitude = $row['Longitude'];
                    $primaryCampusLatitude = $row['Latitude'];
                    
                    //JW: close the statement
                    $primaryCampusDataStatement->close();

                    // Kim: Update primary campus 
                    $updateQuery = "UPDATE `User` SET `PrimaryCampus` = '$primaryCampusID' WHERE `User_ID` = ?";

                    if($updatePrimaryCampusStatement = $connection->prepare($updateQuery)) {
                        if($updatePrimaryCampusStatement->bind_param("i", $id)){
                            if($updatePrimaryCampusStatement->execute()){
                                
                                //JW: close the statement
                                $updatePrimaryCampusStatement->close();


                                // Kim: Delete all subscribeto data for the user
                                $deleteQuery = "DELETE FROM `SubscribedTo` WHERE `User_ID` = ?";

                                if($deleteSubscibeToStatement = $connection->prepare($deleteQuery)) {
                                    if($deleteSubscibeToStatement->bind_param("i", $id)){
                                        if($deleteSubscibeToStatement->execute()){
                                                    
                                            //JW: close the statement
                                            $deleteSubscibeToStatement->close();

                                            //JW: loop through each secondary campus stored in dictionary; query the db for its campus id; store campus id in array
                                            for ($i = 0; $i < $numOfSecondaryCampuses; $i++)
                                            {
                                                $secondaryCampus = $_POST['SecondaryCampus' . $i];

                                                $selectSecQuery = "SELECT `Campus_ID` FROM `Campus` WHERE `Name` = ?";

                                                if($secondaryCampusIdStatement = $connection->prepare($selectSecQuery)) {
                                                    if($secondaryCampusIdStatement->bind_param("s", $secondaryCampus)){
                                                        if($secondaryCampusIdStatement->execute()){

                                                            $result = $secondaryCampusIdStatement->get_result();
                                                            $row = $result->fetch_assoc();

                                                            $secondaryCampusID = $row['Campus_ID'];
                                                                    
                                                            //JW: close the statement
                                                            $secondaryCampusIdStatement->close();

                                                            $insertQuery = "INSERT INTO `SubscribedTo` (`User_ID`, `Campus_ID`) VALUES (?, ?)";

                                                            if($insertSecCampusStatement = $connection->prepare($insertQuery)) {
                                                                if($insertSecCampusStatement->bind_param("is", $id, $secondaryCampusID)){
                                                                    if($insertSecCampusStatement->execute()){
                                                                                
                                                                        //JW: close the statement
                                                                        $insertSecCampusStatement->close();

                                                                        //JW: once everything has finished, if response is still empty, that means everything worked and can return approptiate data
                                                                        if ($i == ($numOfSecondaryCampuses - 1) && $response->wasSuccessful == null) {
                                                                            $response->wasSuccessful = 1;
                                                                            $response->message = "Campuses saved to db -- returning primary campus coordinates";
                                                                            $response->primaryCampusLongitude = $primaryCampusLongitude;
                                                                            $response->primaryCampusLatitude = $primaryCampusLatitude; 
                                                                        }
                                                                    }else{
                                                                        $response->wasSuccessful = 0;
                                                                        $response->message = "insert secondary campus statement has failed to execute";
                                                                    }
                                                                }else{
                                                                    $response->wasSuccessful = 0;
                                                                    $response->message = "insert secondary campus statement has failed to bind parameters";
                                                                }
                                                            }else{
                                                                $response->wasSuccessful = 0;
                                                                $response->message = "insert secondary campus statement has failed to prepare the statement";
                                                            }
                                                        }else{
                                                            $response->wasSuccessful = 0;
                                                            $response->message = "secondary campus name to id statement has failed to execute";
                                                        }
                                                    }else{
                                                        $response->wasSuccessful = 0;
                                                        $response->message = "secondary campus name to id statement has failed to bind parameters";
                                                    }
                                                }else{
                                                    $response->wasSuccessful = 0;
                                                    $response->message = "secondary campus name to id statement has failed to prepare the statement";
                                                }
                                            }
                                        }else{
                                            $response->wasSuccessful = 0;
                                            $response->message = "delete SubscribeTo statement has failed to execute";
                                        }
                                    }else{
                                        $response->wasSuccessful = 0;
                                        $response->message = "delete SubscribeTo statement has failed to bind parameters";
                                    }
                                }else{
                                    $response->wasSuccessful = 0;
                                    $response->message = "delete SubscribeTo statement has failed to prepare the statement";
                                }
                            }else{
                                $response->wasSuccessful = 0;
                                $response->message = "update primary campus statement has failed to execute";
                            }
                        }else{
                            $response->wasSuccessful = 0;
                            $response->message = "update primary campus statement has failed to bind parameters";
                        }
                    }else{
                        $response->wasSuccessful = 0;
                        $response->message = "update primary campus statement has failed to prepare the statement";
                    }
                }else{
                    $response->wasSuccessful = 0;
                    $response->message = "primary campus data statement has failed to execute";
                }
            }else{
                $response->wasSuccessful = 0;
                $response->message = "primary campus data statement has failed to bind parameters";
            }
        }else{
            $response->wasSuccessful = 0;
            $response->message = "primary campus data statement has failed to prepare the statement";
        }
    } else {
        $response->wasSuccessful = 0;
        $response->message = "Unable to upload campuses to db, POST method not used";
    }

    //JW: send response to application via json
    echo json_encode($response);
?>