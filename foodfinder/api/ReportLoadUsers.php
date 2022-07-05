<?php
    /**
     * Retrieves the data from the database to be used for generating a user report.
     *
     * Accepts POST requests only.
     *
     * @param start The date to start the report from
     * @param end The date to end the report at
     * @param level1 Indicates whether General Users should be included. ('true'||'false')
     * @param level2 Indicates whether Event Organisers should be included. ('true'||'false')
     * @param level3 Indicates whether Sustainability Team Users should be included. ('true'||'false')
     *
     * Note: Dates should be in YYYY-MM-DD format for start and end.
     */

    require_once("../util/Dbconnection.php");

    $response = new stdClass();

    if($_SERVER["REQUEST_METHOD"] == "POST"){

        // JH: Get variables
        $timeStart = $_POST['start'];
		$timeEnd   = $_POST['end'];
        $permission1 = $_POST['level1'];
        $permission2 = $_POST['level2'];
        $permission3 = $_POST['level3'];


        // TODO All of these should be using prepared statements
        // JH: Create query depending on variables,
        //      permission levels require these if statements
        $query = "";
        if ($permission1 == "true" && $permission2 == "true" && $permission3 == "false"){
            $query ="SELECT User.Created, PermissionLevel.Description, Campus.Name
                FROM ((PermissionLevel
                INNER JOIN User ON User.HasPermissionLevel = PermissionLevel.Permission_ID)
                INNER JOIN Campus ON User.PrimaryCampus = Campus.Campus_ID) 
                WHERE User.IsDeleted=0 
                AND Created>= '$timeStart' 
                AND Created<= '$timeEnd' 
                AND Permission_ID != 3
                ORDER BY Created ASC";
        } else if ($permission1 == "false" && $permission2 == "true" && $permission3 == "true"){
            $query ="SELECT User.Created, PermissionLevel.Description, Campus.Name
                FROM ((PermissionLevel
                INNER JOIN User ON User.HasPermissionLevel = PermissionLevel.Permission_ID)
                INNER JOIN Campus ON User.PrimaryCampus = Campus.Campus_ID) 
                WHERE User.IsDeleted=0 
                AND Created>= '$timeStart' 
                AND Created<= '$timeEnd' 
                AND Permission_ID != 1
                ORDER BY Created ASC";
        } else if ($permission1 == "true" && $permission2 == "false" && $permission3 == "true"){
            $query ="SELECT User.Created, PermissionLevel.Description, Campus.Name
                FROM ((PermissionLevel
                INNER JOIN User ON User.HasPermissionLevel = PermissionLevel.Permission_ID)
                INNER JOIN Campus ON User.PrimaryCampus = Campus.Campus_ID) 
                WHERE User.IsDeleted=0 
                AND Created>= '$timeStart' 
                AND Created<= '$timeEnd' 
                AND Permission_ID != 2
                ORDER BY Created ASC";
        } else if ($permission1 == "true" && $permission2 == "false" && $permission3 == "false"){
            $query ="SELECT User.Created, PermissionLevel.Description, Campus.Name
                FROM ((PermissionLevel
                INNER JOIN User ON User.HasPermissionLevel = PermissionLevel.Permission_ID)
                INNER JOIN Campus ON User.PrimaryCampus = Campus.Campus_ID) 
                WHERE User.IsDeleted=0 
                AND Created>= '$timeStart' 
                AND Created<= '$timeEnd' 
                AND Permission_ID = 1
                ORDER BY Created ASC";
        } else if ($permission1 == "false" && $permission2 == "true" && $permission3 == "false"){
            $query ="SELECT User.Created, PermissionLevel.Description, Campus.Name
                FROM ((PermissionLevel
                INNER JOIN User ON User.HasPermissionLevel = PermissionLevel.Permission_ID)
                INNER JOIN Campus ON User.PrimaryCampus = Campus.Campus_ID) 
                WHERE User.IsDeleted=0 
                AND Created>= '$timeStart' 
                AND Created<= '$timeEnd' 
                AND Permission_ID = 2
                ORDER BY Created ASC";
        } else if ($permission1 == "false" && $permission2 == "false" && $permission3 == "true"){
            $query ="SELECT User.Created, PermissionLevel.Description, Campus.Name
                FROM ((PermissionLevel
                INNER JOIN User ON User.HasPermissionLevel = PermissionLevel.Permission_ID)
                INNER JOIN Campus ON User.PrimaryCampus = Campus.Campus_ID) 
                WHERE User.IsDeleted=0 
                AND Created>= '$timeStart' 
                AND Created<= '$timeEnd' 
                AND Permission_ID = 3
                ORDER BY Created ASC";
        } else if ($permission1 == "false" && $permission2 == "false" && $permission3 == "false") {
            $query ="SELECT User.Created, PermissionLevel.Description, Campus.Name
                FROM ((PermissionLevel
                INNER JOIN User ON User.HasPermissionLevel = PermissionLevel.Permission_ID)
                INNER JOIN Campus ON User.PrimaryCampus = Campus.Campus_ID) 
                WHERE User.IsDeleted=0 
                AND Created>= '$timeStart' 
                AND Created<= '$timeEnd' 
                AND Permission_ID = -1
                ORDER BY Created ASC";
        } else {
            $query ="SELECT User.Created, PermissionLevel.Description, Campus.Name
                FROM ((PermissionLevel
                INNER JOIN User ON User.HasPermissionLevel = PermissionLevel.Permission_ID)
                INNER JOIN Campus ON User.PrimaryCampus = Campus.Campus_ID) 
                WHERE User.IsDeleted=0 
                AND Created>= '$timeStart' 
                AND Created<= '$timeEnd' 
                AND Permission_ID < 4
                ORDER BY Created ASC";
        }


        $response = [];
        //JH: check if there were any results from the db
        if ($result = $connection->query($query)) {

            $i = 0;

            //JH: stores all the results into tuples
            while($row = $result->fetch_assoc()){
                $response[$i] = new stdClass();
                $response[$i]->Time = $row["Created"];
                $response[$i]->Permission = $row["Description"];
				$response[$i]->Campus = $row["Name"];

                $i++;
            }
            $result->free();
            
        } else {
            $response->wasSuccessful = 0;
            $response->message = "No users were found that match the options chosen";
        }
    } else {
        $response->wasSuccessful = 0;
        $response->message = "Unable to fetch events from db, GET method not used";
    }
    echo json_encode($response);
?>