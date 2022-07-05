<?php
/**
 * Sends an email address to Sustainability Email Addresses
 *
 * Accepts POST requests only.
 *
 * @param EmailAddress The email of the user making the request.
 * @param RequestReason The reason that was provided by the user.
 */
 require_once("../util/Dbconnection.php");
  //Kim: Mail service allow this script to send email
 require_once ('../phpmailer/PHPMailerAutoload.php');

 //Kim: Host email information
 require_once("../util/hostEmail.php");

 //Kim: Object to hold response information
 $response = new stdClass();

 if($_SERVER["REQUEST_METHOD"]== "POST")
 {
     $requestEmail = $_POST["EmailAddress"];
     $requestReason = $_POST["RequestReason"];

     //Kim: Select all email address of Sustainability Team users
     $selectEmailsQuery = "SELECT `EmailAddress` FROM `User` WHERE `HasPermissionLevel` = 3 AND `IsDeleted` = 0 AND `Activated` = 1 AND `IsSubscribedToRequests` = 1";
     $stmt = $connection->prepare($selectEmailsQuery);
     $stmt->execute();

     // JN: Check for errors on the query and the connection
     $error = "";
     $error = $stmt->error.$connection->error;

    // JA: Variable to be converted to JSON
    $sustainabilityEmails = [];

    if ($error == "") {
        if ($result = $stmt->get_result()) {
            $i = 0; // JA: Iterator

            // JA: Iterate through the result rows and add them to the response object
            while ($row = $result->fetch_assoc()) {
                // JA: Initialise the object for JSON
                $sustainabilityEmails[$i] = new stdClass();

                // JA: Add the retrieved email to the response object
                $sustainabilityEmails[$i] = $row["EmailAddress"];

                // JA: Iterate to the next record
                $i++;
            }
            $result->free();
        }

        // Kim: Success response
        $response->wasSuccessful = 1;
        $response->message = "Request reason was sent through email";
    }
    else {
        // JN: Bad response
        $response->wasSuccessful = 0;
        $response->message = $error;
    }

    if (!empty($sustainabilityEmails)) {
        // Kim: Create mail
        $mail = new PHPMailer();

        // Kim: SMTP certificate
        $mail ->IsSMTP();
        $mail ->SMTPDebug =2;
        $mail ->SMTPAuth = true;
        $mail ->SMTPSecure = "tls";
        $mail ->Host = "smtp.live.com";
        $mail ->Port =587; // 465 or 587
        $mail ->IsHTML(true);

        //Kim: Host email and password
        $mail ->Username = $HOSTEMAIL;
        $mail ->Password = $HOSTEMAILPASSWORD;

        // Kim: Set "email from" to host's email
        $mail ->setFrom($HOSTEMAIL,'Food Finder');
        $mail ->Subject = "New Food Finder Permission Request";

        // Kim: Email body message
        $message = '<p>There is a new request from '.$requestEmail.' to become an Event Organiser.<br>'.
            'Their reason for the request is:</p>'.
            '<p>'.$requestReason.'</p>'.
            '<p>Regards,<br>'.
            'Food Finder</p>'.
            '<p style="font-size:10pt;line-height:10pt;font-family:Calibri,sans-serif">You are receiving this email as you are a member of the Sustainability Team. If you wish to stop receiving these you can unsubscribe in the admin page of the Food Finder web-portal.</p>';

        // Kim: Email body
        $mail ->Body = "<html>\n";
        $mail ->Body .= "<body style=\"font-family:Verdana, Verdana, Geneva, sans-serif; font-size:12px; color:#666666;\">\n";
        $mail ->Body = $message;
        $mail ->Body .= "</body>\n";
        $mail ->Body .= "</html>\n";

        // Kim: Additional information for clarification of source
        $mail ->Headers = "From: ".$HOSTEMAIL."\r\n";
        $mail ->Headers .= "Reply-To: ".$HOSTEMAIL."\r\n";
        $mail ->Headers .= "Return-Path: ".$HOSTEMAIL."\r\n";

        for($i=0; $i<count($sustainabilityEmails); $i++) {
            //Kim: Add all email addresses to "send email to"
            $mail ->AddBCC($sustainabilityEmails[$i]);
        }

        // Kim: Function that sends email
        if ($mail ->Send()) {
            $response->wasSuccessful = 1;
            $response->message = "Email sent";
        }
        else
        {
            $response->wasSuccessful = 0;
            $response->message = "Email not sent";

        }
    }
    else {
        $response->wasSuccessful = 0;
        $response->message = "No email address were found";
    }

    $stmt->close();
 }
 else {
    // Kim:: Post method not used
    $response->wasSuccessful = 0;
    $response->message = "POST method not used";
 }

 $JSON = json_encode($response);
 echo $JSON;