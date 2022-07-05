<?php
    /**
     * User: Justin
     * Created Date: 13/08/2018
     * Time: 10:48 AM
     * Updated: 13/08/2018
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
        $wasDeleted = $_POST['deleted'];
        if ($wasDeleted == "true") {
            $wasDeleted = "1";
        }

        //TODO This should be using prepared statements
        // JH: Create query for Event data with variables
        $query ="SELECT Event.Name as EName, Campus.Name, Event.StartTime 
			FROM ((Event 
			INNER JOIN Room ON Event.LocatedIn = Room.Room_ID) 
			INNER JOIN Campus ON Room.Campus=Campus.Campus_ID)
			WHERE Event.StartTime >= '$timeStart'
			AND Event.StartTime <= '$timeEnd' 
			AND Event.Deleted <= '$wasDeleted'
			ORDER BY Event.StartTime ASC ";

        $response = [];
        //JH: check if there were any results from the db
        if ($result = $connection->query($query)) {

            $i = 0;

            //JH: stores all the results into tuples
            while($row = $result->fetch_assoc()){
                $response[$i] = new stdClass();
                $response[$i]->Name = $row["EName"];
                $response[$i]->Campus = $row["Name"];
                $response[$i]->Event_Time = $row["StartTime"];
                $i++;
            }
            $result->free();
            
        } else {
            $response->wasSuccessful = 0;
            $response->message = "No events found in the database that match these options";
        }
    } else {
        $response->wasSuccessful = 0;
        $response->message = "Unable to fetch events from db, GET method not used";
    }
    echo json_encode($response);
?>