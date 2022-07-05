<?php
/**
 * User: Kim
 * Date: 17/08/18
 * Time: 10.00 AM
 *
 * Kim: This php will send new password to the user who requested "forgot password" from ForgotPassword page.
 *    
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
    $email = $_POST["EmailAddress"];

    //Kim: select salt and email from database
    $selectQuery ="SELECT `EmailAddress`,`Salt` FROM `User` WHERE `EmailAddress` = ?";
    $stmt = $connection->prepare($selectQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    //Kim: Get result
    $result = $stmt->get_result();
    //Kim: Set empty strings
    $email = "";
    $salt = "";

    if ($result->num_rows > 0)
    {
   
        $row = $result->fetch_assoc();
        //Kim: get random password
        $newPassword = GetPassword();

    
        $salt = $row['Salt'];
        $email = $row['EmailAddress'];

        //Kim: hash the new password
        $fPassword = hash('sha256', $salt.$newPassword);
        //Kim: update the password to new temporary password
        $updateQuery = "UPDATE `User` SET `Password` = '$fPassword' WHERE `EmailAddress` = ?";
        $stmt = $connection->prepare($updateQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();



        // Kim: create mail 
        $mail = new PHPMailer();

        // Kim: SMTP certificate
        $mail ->IsSMTP();
        $mail ->SMTPDebug = 2;
        $mail ->SMTPAuth = true;
        $mail ->SMTPSecure = "tls";
        $mail ->Host = "smtp.live.com";
        $mail ->Port =587; // or 587
        $mail ->IsHTML(true);

        //Kim: Host email and password
        $mail ->Username = $HOSTEMAIL;
        $mail ->Password = $HOSTEMAILPASSWORD;

        // Kim: set "email from" to host's email
        $mail ->setFrom($HOSTEMAIL,'Food Finder');
        $mail ->Subject = "Food Finder Password Reset";

        // Kim: email body message
        $message = '<p>This account\'s password has been reset.<br>'.
            'Please login to the Food Finder application using your new password.</p>'.
            '<p>New password: '.$newPassword.'</p>'.
            '<p>Regards,<br>'.
            'Food Finder</p>'.
            '<p style="font-size:10pt;line-height:10pt;font-family:Calibri,sans-serif">Remember to change your password to something more secure in the preferences section of the Food Finder application.</p>';

        // Kim: email body
        $mail ->Body = "<html>\n";
        $mail ->Body .= "<body style=\"font-family:Verdana, Verdana, Geneva, sans-serif; font-size:12px; color:#666666;\">\n";
        $mail ->Body = $message;
        $mail ->Body .= "</body>\n";
        $mail ->Body .= "</html>\n";

        //Kim; send email to
        $mail ->AddBCC($email);

        // Kim: additional information for clarification of source
        $mail ->Headers = "From: ".$HOSTEMAIL."\r\n";
        $mail ->Headers .= "Reply-To: ".$HOSTEMAIL."\r\n";
        $mail ->Headers .= "Return-Path: ".$HOSTEMAIL."\r\n";
        $mail ->Headers .= "CC: ".$HOSTEMAIL."\r\n";
        $mail ->Headers .= "BCC: ".$HOSTEMAIL."\r\n";

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
        $response->message = "New password was sent successfully through email";
 
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
function GetPassword() { //https://codereview.stackexchange.com/questions/92869/php-salt-generator
    $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'; 
    $randStringLen = 8;

    $randString = "";
    for ($i = 0; $i < $randStringLen; $i++) {
        $randString .= $charset[mt_rand(0, strlen($charset) - 1)];
    }

    return $randString;
}
$JSON = json_encode($response);
echo $JSON;