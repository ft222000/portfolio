<?php
/**
 * Fetches the information required for the feedback report.
 *
 * Accepts POST requests only.
 *
 * @param start The first date that the report should include (YYYY:mm:dd HH:mm)
 * @param end The last date that the report should include (YYYY:mm:dd HH:mm)
 * @param deleted ("true" || "false") Determines if the report should include deleted feedback
 */

require_once("../util/Dbconnection.php");

$response = [];
//require_once("../util/verboseLogging.php");

if($_SERVER["REQUEST_METHOD"] == "POST"){

    // JH: Get variables
    $timeStart  = $_POST['start'];
    $timeEnd    = $_POST['end'];
    $isDeleted  = $_POST['deleted'];
    if ($isDeleted == "true") {
        $isDeleted = 1;
    } else {
        $isDeleted = 0;
    }

    // JH: Create query for Usage data with variables
    $query ="SELECT F.SubmittedTime as `Time Stamp`,
        U.EmailAddress as `Email Address`,
        C.Name as `Campus`,
        F.FeedbackMessage as `Feedback`
        FROM Feedback as F
        INNER JOIN User as U ON F.User_ID = U.User_ID
        INNER JOIN Campus as C ON U.PrimaryCampus = C.Campus_ID
        WHERE F.SubmittedTime >= ?
        AND F.SubmittedTime <= ?
        AND F.IsDeleted <= ?
        ORDER BY F.SubmittedTime ASC";

    if($stmt = $connection->prepare($query)) {
        if($stmt->bind_param("ssi", $timeStart, $timeEnd, $isDeleted)) {
            if ($stmt->execute()) {
                $response = [];
                if ($result = $stmt->get_result()) {
                    $i = 0;

                    //JH: stores all the results into tuples
                    while($row = $result->fetch_assoc()){
                        $response[$i] = new stdClass();
                        $response[$i]->Time = $row["Time Stamp"];
                        $response[$i]->Email = $row["Email Address"];
                        $response[$i]->Campus = $row["Campus"];
                        $response[$i]->Feedback = $row["Feedback"];
                        $i++;
                    }

                    $result->free();
                }
            }
        }
    }
}
echo json_encode($response);
?>