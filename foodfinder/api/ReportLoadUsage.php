<?php
    /**
     * User: Justin
     * Created Date: 18/08/2018
     * Time: 8:36 PM
     * Updated: 18/08/2018
     *
     * a test file to run sql commands and send them to the reports file.
     */

    require_once("../util/Dbconnection.php");

    $response = new stdClass();
	//require_once("../util/verboseLogging.php");

    if($_SERVER["REQUEST_METHOD"] == "POST"){

        // JH: Get variables
		$timeStart  = $_POST['start'];
		$timeEnd    = $_POST['end'];

		// TODO This should be using prepared statements
		// JH: Create query for Usage data with variables
        $query ="SELECT Campus.Name, Event.StartTime
            FROM (((Attends
            INNER JOIN Event ON Attends.Event_ID = Event.Event_ID) 
            INNER JOIN Room ON Event.LocatedIn = Room.Room_ID) 
            INNER JOIN Campus ON Room.Campus=Campus.Campus_ID)
            WHERE Event.StartTime >= '$timeStart'
            AND Event.StartTime <= '$timeEnd'
            ORDER BY Event.StartTime ASC";

        $response = [];
        //JH: check if there were any results from the db
        if ($result = $connection->query($query)) {

            $i = 0;

            //JH: stores all the results into tuples
            while($row = $result->fetch_assoc()){
                $response[$i] = new stdClass();
                $response[$i]->Campus = $row["Name"];
                $response[$i]->Event_Time = $row["StartTime"];
                $i++;
            }
            $result->free();
            
        } else {
            $response->wasSuccessful = 0;
            $response->message = "No usage information found for the options chosen";
        }
    } else {
        $response->wasSuccessful = 0;
        $response->message = "Unable to fetch events from db, GET method not used";
    }
    echo json_encode($response);
?>