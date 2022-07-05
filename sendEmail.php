<?php 
  
    // Kim: create mail 
    $mail = new PHPMailer();

    // Kim: SMTP certificate
    $mail ->IsSMTP();
    $mail ->SMTPDebug =2;
    $mail ->SMTPAuth = true;
    $mail ->SMTPSecure = "tls";
    $mail ->Host = "smtp.live.com";
    $mail ->Port =587; // 465 or 587 
    $mail ->IsHTML(false);

    $mail ->Username = "gljin@utas.edu.au";
    $mail ->Password = "Smith123University1";
    // Kim: set email to user's email
    $mail ->setFrom('gljin@utas.edu.au','FoodFinder');
   
   
   
    $mail ->Subject = "test mail";
    $mail ->Body = "test";
    //Kim; send email to
    $mail ->AddBCC("gljin@utas.edu.au");


    // Kim: addtional information for clarification of source
    $mail ->Headers = "From: gljin@utas.edu.au\r\n";
    $mail ->Headers .= "Reply-To: gljin@utas.edu.au\r\n";
    $mail ->Headers .= "Return-Path: gljin@utas.edu.au\r\n";
    $mail ->Headers .= "CC: gljin@utas.edu.au\r\n";
    $mail ->Headers .= "BCC: gljin@utas.edu.au\r\n";
    

    // Kim: function that sends email
    if ($mail ->Send())
    {
        $response->wasSuccessful = 1;
        $response->message = "Email sent ++++++++++";
    }
    else
    {
        $response->wasSuccessful = 0;
        $response->message = "Email fail +++++++++++++++";

    }
?>