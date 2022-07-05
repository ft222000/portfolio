<?php
/**
 * Sends an email to all users that the event would be relevant to.
 *
 * Accepts POST requests only.
 *
 * @param Name The name of the event.
 * @param StartTime The time that the event starts.
 * @param Campus The ID of the campus that the event is on.
 * @param EventID The ID of the event.
 * @param LocatedIn The name of the room/building that the event is held in.
 */
//Kim: Mail service allow this script to send email
require_once ('../phpmailer/PHPMailerAutoload.php');
require_once("../util/Dbconnection.php");

//Kim: Host email information
require_once("../util/hostEmail.php");

//Kim: Object to hold response information
$response = new stdClass();
if($_SERVER["REQUEST_METHOD"]== "POST")
{
    $emailState = 1;
    $name = $_POST["Name"]; // The name of the event
   // $organiser = $_POST["Organiser"]; // The organiser of the event
    $eventStartTime = $_POST["StartTime"]; // The start time of the event
    $campusID = (int)$_POST["Campus"]; // The ID of the campus that is associated with the event
    $eventID = (int)$_POST["EventID"]; // The ID of the event
    $location = $_POST["LocatedIn"]; // The
    $tagsSentence ="";
    $activated = 1;


    //Kim: Formatted event start time and event end time to be readable
    $eventStartDate = date("d/m/Y",strtotime($eventStartTime));
    $eventStartTime = date("H:i", strtotime($eventStartTime));

    //Kim: SQL statement: Grab user emails by the selected Event campus name with Email switch on
    $selectEmailQuery ="SELECT `EmailAddress` FROM `User` JOIN `SubscribedTo` ON User.User_ID=SubscribedTo.User_ID JOIN PreferredNotification ON User.User_ID = PreferredNotification.User_ID WHERE SubscribedTo.Campus_ID = ? AND PreferredNotification.Notification_ID = ? AND User.Activated =?";
    $stmt = $connection->prepare($selectEmailQuery);
    $stmt->bind_param("iii", $campusID,$emailState,$activated);
    $stmt->execute();

    //Kim: Get result
    $result = $stmt->get_result();

    if($result->num_rows > 0)
    {
        //Kim: Store emails to an array
        $emailAll = array();
        while($row = $result->fetch_array())
        {

            $emailAll[] = $row['EmailAddress'];
        }

        //JA: Defaults the campusName to an empty string
        $campusName = "";

        // Kim: select campus name from the campus ID
        $selectCampusQuery ="SELECT `Name` FROM `Campus` WHERE `Campus_ID`= ?";
        $stmt = $connection->prepare($selectCampusQuery);
        $stmt->bind_param("i", $campusID);
        $stmt->execute();

        //Kim: Get result
        $result = $stmt->get_result();
        if($result->num_rows > 0)
        {
            $row = $result->fetch_assoc();
            $campusName = $row['Name'];
        }
        else
        {
            $response->wasSuccessful = 0;
            $response->message = "Fail to find campus name";
        }

        // Kim: select tags from the event ID
        $selectTagQuery ="SELECT `Description` FROM `FoodTag` JOIN `Describes` ON FoodTag.Tag_ID=Describes.Tag_ID WHERE Describes.Event_ID = ?";
        $stmt = $connection->prepare($selectTagQuery);
        $stmt->bind_param("i", $eventID);
        $stmt->execute();

         //Kim: Get result
        $result = $stmt->get_result();
        //Kim: with each number of tag, show different text
        switch($result->num_rows){
            case 0:
                $tagsSentence=".";
            break;
            case 1:
                $row = $result->fetch_assoc();
                $tag = $row['Description'];
                $tagsSentence = "&nbspand the type of food is ".$tag.'.';
            break;
            default:
                $tagsSentence = "&nbspand the types of food are ";
                $tagAll = array();
                while($row = $result->fetch_assoc())
                {
                    $tagAll[] = $row['Description'];
                }

                foreach($tagAll as $value)
                {
                    $tagsSentence .=$value.", ";
                }
                //Kim: delete last comma
                $tagsSentence= substr(trim($tagsSentence), 0, -1);
                //JA: Add a period
                $tagsSentence .= '.';
            break;
        }

        // Kim: create mail
        $mail = new PHPMailer();

        // Kim: SMTP certificate
        $mail ->IsSMTP();
        $mail ->SMTPDebug =1;
        $mail ->SMTPAuth = true;
        $mail ->SMTPSecure = "tls";
        $mail ->Host = "smtp.live.com";
        $mail ->Port =587; // 465 or 587
        $mail ->IsHTML(true);

        //Kim: Host email and password
        $mail ->Username =$HOSTEMAIL;
        $mail ->Password = $HOSTEMAILPASSWORD;
        // Kim: set "email from" to host's email
        $mail ->setFrom($HOSTEMAIL,'Food Finder');
        $mail ->Subject = "New Food Finder Event - ".$name;

        // Kim: email body message
        $message = '<p>There is food available on the '.$campusName.' campus, located at '.$location.'.'.
            ' The event starts on the '.$eventStartDate.' at '.$eventStartTime.''.$tagsSentence.'<br>'.
            ' Find more information about this event in the Food Finder application.</p>'.
            '<p>Regards,<br>'.
            'Food Finder</p>'.
            '<p style="font-size:10pt;line-height:10pt;font-family:Calibri,sans-serif">If you wish to unsubscribe from these emails, you can unsubscribe in the preferences section of the Food Finder application.</p>';

        // Kim: email body
        $mail ->Body = "<html>\n";
        $mail ->Body .= "<body style=\"font-family:Verdana, Verdana, Geneva, sans-serif; font-size:12px; color:#666666;\">\n";
        $mail ->Body = $message;
        $mail ->Body .= "</body>\n";
        $mail ->Body .= "</html>\n";


        // Kim: additional information for clarification of source
        $mail ->Headers = "From: ".$HOSTEMAIL."\r\n";
        $mail ->Headers .= "Reply-To: ".$HOSTEMAIL."\r\n";
        $mail ->Headers .= "Return-Path: ".$HOSTEMAIL."\r\n";
        $mail ->Headers .= "CC: ".$HOSTEMAIL."\r\n";
        $mail ->Headers .= "BCC: ".$HOSTEMAIL."\r\n";

        for($i=0;$i<count($emailAll);$i++)
        {
            //Kim: add all email addresses to "send email to
            $mail ->AddBCC($emailAll[$i]);
        }
        // Kim: function that sends email
        if ($mail ->Send())
        {
            $response->wasSuccessful = 1;
            $response->message = "Email sent";
        }
        else
        {
            $response->wasSuccessful = 0;
            $response->message = "Email sent fail";
        }
        //Kim: free email for next use
        $mail->ClearAddresses();
        //Kim: free the array for next use.
        unset($emailAll);
    }
    else
    {
        $response->wasSuccessful = 0;
        $response->message = "Email address is not found";
    }

        // JN: Check for errors on the query and the connection
    $error = "";
    $error = $stmt->error.$connection->error;

    $stmt->close();

    if ($error == "") {
        // Kim: Success response
        $response->wasSuccessful = 1;
        $response->message = "New event was sent through email";

    } else {
        // JN: Bad response
        $response->wasSuccessful = 0;
        $response->message = $error;
    }
}
else
{
    // Kim:: Post method not used
    $response->wasSuccessful = 0;
    $response->message = "Post connection Fail";
}
$JSON = json_encode($response);
echo $JSON;
