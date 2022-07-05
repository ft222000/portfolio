<?php
/**
 * Created by PhpStorm.
 * User: Solfire
 * Date: 10/08/2018
 * Time: 9:51 PM
 *
 * This api will obtain a list of images that are based on a event id passed through from the app
 */

require_once ("../util/Dbconnection.php");

//JA: Check to see what kind of request is being used.
$method = $_SERVER['REQUEST_METHOD'];

//JA: Object to hold the response information
$response = new stdClass();

if($method == "POST"){
    //JA: Get variables
    $event = (int)$_POST["Event"];

    $imageQuery = "SELECT `Data` FROM `EventImage` WHERE `Event` = ?";
    $stmt = $connection->prepare($imageQuery);
    $stmt->bind_param("i",$event);

    if (!$stmt){
        // Error
        $error = $error.$stmt->error;
    } {
        // JA: Check if there were any matching results
        if ($stmt->execute()){
            $response = [];

            if ($result = $stmt->get_result()){
                $i = 0;

                // JA: Stores all results into tuples
                while ($row = $result->fetch_assoc()){
                    $response[$i] = new stdClass();
                    $response[$i]->Data = $row["Data"];
                    $i++;
                }

                // JA: Free the result
                $result->free();
            }
        } else {
            $error = $error.$stmt->error;
        }
    }

    //JA: Check for errors on the query and the connection
    $error = "";
    $error = $stmt->error.$connection->error;
    $stmt->close();

    if ($error == ""){
        //JA: Success response
        $response->wasSuccessful = 1;
        $response->message = "Event images were retrieved";
    } else {
        //JA: Bad response
        $response->wasSuccessful = 0;
        $response->message = $error;
    }
} else {
    //JA: Post method not used
    $response->wasSuccessful = 0;
    $response->message = "Event images were not retrieved, POST method not used";
}

//JA: Check to see if this request came form a logged in user (Prevents cross domain requests)
$JSON = json_encode($response);
echo $JSON;