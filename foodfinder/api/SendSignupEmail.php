<?php
    /**
     * Sends a verification code to the user who just created an account.
     *
     * Accepts POST requests only.
     *
     * @param Email The email address of the user that is attempting to sign up.
     *
     * Note: The verification code is based on the starting characters of the users salt in the Users table of the database.
     */
   
    require_once("../util/Dbconnection.php");
   
    //Kim: Mail service allow this script to send email
    require_once ('../phpmailer/PHPMailerAutoload.php');
    //Kim: Object to hold response information
    $response = new stdClass();
    //Kim: Host email information
    require_once("../util/hostEmail.php");
    // Kim: Gets the type of request received
    if($_SERVER["REQUEST_METHOD"] == "POST")
    {

        $email = $_POST['Email'];

        //Kim: Select the session user's salt 
        $selectQuery ="SELECT `Salt` FROM `User` WHERE `EmailAddress` = ?";
        $stmt = $connection->prepare($selectQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute(); 
        //Kim: Get result
        $result = $stmt->get_result();
                
        if ($result->num_rows > 0) 
        {
            $row = $result->fetch_assoc();
            $salt = $row['Salt'];
            //Kim: Select first five characters as verification code
            $salt = substr($salt,0,5); 

            // Kim: create mail 
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
            $mail ->Username =$HOSTEMAIL;
            $mail ->Password = $HOSTEMAILPASSWORD;
            // Kim: set "email from" to host's email
            $mail ->setFrom($HOSTEMAIL,'Food Finder');
            $mail ->Subject = "Food Finder Verification Code";

            //Kim; send email to
            $mail ->AddBCC($email);

            // Kim: email body message
            $message = '<p>A new Food Finder account has been created using this email.<br>'.
                'Please confirm this is the correct email by entering the verification code in the Food Finder application.</p>'.
                '<p>Verification code: '.$salt.'</p>'.
                '<p>Thank you for choosing the Food Finder application!</p>'.
                '<p>Regards,<br>'.
                'Food Finder</p>'.
                '<p style="font-size:10pt;line-height:10pt;font-family:Calibri,sans-serif">If you did not create a new Food Finder account then you can safely disregard this email.</p>';

            // Kim: email body
            $mail ->Body = "<html>\n";
            $mail ->Body .= "<body >\n";
            $mail ->Body = $message;
            $mail ->Body .= "</body>\n";
            $mail ->Body .= "</html>\n";


            // Kim: additional information for clarification of source
            $mail ->Headers = "From: ".$HOSTEMAIL."\r\n";
            $mail ->Headers .= "Reply-To: ".$HOSTEMAIL."\r\n";
            $mail ->Headers .= "Return-Path: ".$HOSTEMAIL."\r\n";
            $mail ->Headers .= "CC: ".$HOSTEMAIL."\r\n";
            $mail ->Headers .= "BCC: ".$HOSTEMAIL."\r\n";
    

            // Kim: function that sends email
            if ($mail ->Send())
            {
                // Kim: Success response
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
            $response->message = "Unable to find salt";
        }
    }
    else
    {
        $response->wasSuccessful = 0;
        $response->message = "Unable to use POST";
    
    }
    echo json_encode($response);
    ?>