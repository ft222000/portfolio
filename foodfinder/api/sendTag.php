<?php
/**
 * Retrieves a list of food tags that are available.
 *
 * Accepts GET requests only.
 */
require_once("../util/Dbconnection.php");

$response = new stdClass();

if($_SERVER["REQUEST_METHOD"] == "GET"){

    $query ="SELECT `Tag_ID`,`Description` FROM `FoodTag` WHERE isDeleted = 0";

    $response = [];
    //JW: check if there were any results from the db
    if ($result = $connection->query($query)) {

        $i = 0;

        //JW: stores all the results into tuples
        while($row = $result->fetch_assoc()){
            $response[$i] = new stdClass();
            $response[$i]->Description = $row["Description"];
            $response[$i]->Tag_Id = $row["Tag_ID"];
            $i++;
        }
        $result->free();

    } else {
        $response->wasSuccessful = 0;
        $response->message = "No tags found in the db";
    }
} else {
    $response->wasSuccessful = 0;
    $response->message = "Unable to fetch tags from db, GET method not used";
}
echo json_encode($response);
?>