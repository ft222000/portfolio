<?php
/**
 * Saves an image to an event.
 *
 * Accepts POST requests only.
 *
 * @param Data The image to be stored to the database. (Base64 String)
 * @param Event The id of the event that the image is being added to.
 *
 * Note: Images should be heavily compressed before being passed to this API.
 */

require_once ("../util/Dbconnection.php");

//JA: Check to see what kind of request is being used.
$method = $_SERVER['REQUEST_METHOD'];

//JA: Object to hold the response information
$response = new stdClass();

if($method == "POST"){
    //JA: Get variables
    $data = $_POST["Data"];
    $event = (int)$_POST["Event"];

    //JA: Expected variables for binding (Data, Event)
    $imageQuery = "INSERT INTO `EventImage` (`Data`, `Event`) VALUES (?, ?)";
    $stmt = $connection->prepare($imageQuery);
    $stmt->bind_param("si",$data, $event);
    $stmt->execute();

    //JA: Check for errors on the query and the connection
    $error = "";
    $error = $stmt->error.$connection->error;
    $stmt->close();

    if ($error == ""){
        //JA: Success response
        $response->wasSuccessful = 1;
        $response->message = "Event image was created";
    } else {
        //JA: Bad response
        $response->wasSuccessful = 0;
        $response->message = $error;
        $response->data = $data;
        $response->eventID = $event;
    }
} else {
    //JA: Post method not used
    $response->wasSuccessful = 0;
    $response->message = "Event image was not created, POST method not used";
}

//JA: Check to see if this request came form a logged in user (Prevents cross domain requests)
$JSON = json_encode($response);
echo $JSON;