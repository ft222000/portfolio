<?php
/**
 * Sets the event tags for a given event.
 *
 * Accepts POST requests only.
 *
 * @param Event The id of the event that the tags should be added to.
 * @param TagCount How many tags will be passed to the database.
 * @param SelectedTagsX  The name of the tag to be added to the event. (Replace X with numbers 0 to TagCount)
 */

require_once ("../util/Dbconnection.php");

$method = $_SERVER["REQUEST_METHOD"];

$response = new stdClass();

if($method == "POST"){
    //EDH: Get variables
    $eventId = $_POST['Event'];
    $numOfTags = $_POST['TagCount'];

    //EDH: Delete tags that are already assigned to event, if they exist.
    $deleteEventTagsQuery = "DELETE FROM `Describes` WHERE `Event_ID` = '$eventId'";
    mysqli_query($connection, $deleteEventTagsQuery);

    //JW: loop through each selected tags stored in dictionary; query the db for its tag id; store tag id in array
    for ($i = 0; $i < $numOfTags; $i++){
        $tag = $_POST['SelectedTags' . $i];

        $tagQuery = "SELECT `Tag_ID` FROM `FoodTag` WHERE `Description` = '$tag'";
        $tagId_result = mysqli_query($connection, $tagQuery);

        if ($tagId_result->num_rows > 0) {
            $row = $tagId_result->fetch_assoc();

            $tagId[] = $row['Tag_ID'];
        }
    }

    // Kim & JW: loop over tag id array and insert them into the Describes table
    foreach($tagId as $value){
        //Expected variables for binding (Event_ID, Tag_ID)
        $insertQuery = "INSERT INTO `Describes` (`Event_ID`, `Tag_ID`) VALUES (?, ?)";
        $stmt = $connection->prepare($insertQuery);
        $stmt->bind_param("ii",$eventId,$value);
        $stmt->execute();
    }

    $error = "";
    $stmt = $stmt.$error.$connection->error;
    $stmt->close();

    if ($error == ""){
        //EDH: Success response
        $response->wasSuccessful = 1;
        $response->message = "Tags saved to db";
    } else {
        //EDH: Bad response
        $response->wasSuccessful = 0;
        $response->message = $error;
    }
} else {
    $response->wasSuccessful = 0;
    $response->message = "Unable to upload tags to db, POST method not used";
}

$JSON = json_encode($response);
echo $JSON;