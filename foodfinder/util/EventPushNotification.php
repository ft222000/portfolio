<?php
    /*
        Routinely checks the database and pushes out notifications to users for events that have started.

        This php script requires php-curl, and needs to be run via a cron job. If using ubuntu, run with the www-data user:
        
        sudo crontab -u www-data -e

        * * * * * php /var/www/html/util/EventPushNotification.php
    */
    
    require_once("Dbconnection.php");

    //JW: used for sql bind_params
    $NOTIFICATION_NOT_SENT = 0;
    $NOTIFICATION_SENT = 1;

    //JW: current datetime, example: 2018-09-30 10:05:43, same as db
    $datetime = date("Y-m-d H:i:s");
    //JW: Keeps track of events
    $eventCount = 0;

    echo "looking for events that started prior to " . $datetime . " | ";

    //TE: This query Returns all events and the name of the campus they are at if a notification has not been sent for the event. JOINS through ROOM and CAMPUS
	$unnotifiedEventsQuery = "SELECT Event.Event_ID, Campus.Name
								FROM `Event`
								INNER JOIN	`Room` ON Room.Room_ID = Event.LocatedIn
								INNER JOIN	`Campus` ON Campus.Campus_ID = Room.Campus
                                WHERE Event.NotificationSent = ? AND Event.StartTime <= ?";

    if($obtainEventInfoStatement = $connection->prepare($unnotifiedEventsQuery)){
        if($obtainEventInfoStatement->bind_param("is", $NOTIFICATION_NOT_SENT, $datetime)){			
            if($obtainEventInfoStatement->execute()){

                $result = $obtainEventInfoStatement->get_result();

                //JW: close query
                $obtainEventInfoStatement->close();

                //loop through all results, store in array
                while ($row = $result->fetch_assoc()) {
                    $subscription[] = $row['Name']; //JW: Store campus name to send through notificiation id
                    $eventId[] = $row['Event_ID']; //JW: Store event id to send through notificiation id
                }

                echo "finished obtainEventInfoStatement | ";

                //JW: Mark each of the events as sent in the database
                $markAsSentQuery = "UPDATE `Event` SET `NotificationSent` = ? WHERE `Event_ID` = ?";

                if (!empty($eventId)) {
                    foreach ($eventId as $event) {
                        if($markAsSentStatement = $connection->prepare($markAsSentQuery)){				
                            if($markAsSentStatement->bind_param("is", $NOTIFICATION_SENT, $event)){
                                if($markAsSentStatement->execute()){
        
                                    //JW: close query
                                    $markAsSentStatement->close();
        
                                    SendPushNotification($subscription[$eventCount]);
        
                                    echo "finished markAsSentStatement for event on campus " . $subscription[$eventCount] . " | ";
                                    
                                    $eventCount++;
                                }
                                else {
                                    echo "failed to execute markAsSentStatement";
                                }
                            } 
                            else {
                                echo "failed to bind paramenters of markAsSentStatement";
                            }
                        }
                        else {
                            echo "failed to prepare markAsSentStatement";
                        }
                    }
                } 
                else {
                    echo "no new events";
                }
            }
            else {
                echo "failed to execute obtainEventInfoStatement";
            }
        } 
        else {
            echo "failed to bind paramenters of obtainEventInfoStatement";
        }
    }
    else {
        echo "failed to prepare obtainEventInfoStatement";
    }

    /**
     * This function sends a push notification to all users who have subscribed
     * to the campus the event is being hosted at.
     *
     * @param string $subscription
     * @return void
     */
    function SendPushNotification($subscription) {
        $url = "https://fcm.googleapis.com/fcm/send";
        $serverKey = "AAAAXdFzKcA:APA91bHA3jErNpIDywUf4fRLtY43XiVI_1INHIYfF7Jv_HuDaGPl9A8eMG_qtdui7sseOCmKtQbJt2zTVe3FJ69pBUnkssVwJQISDzSEVHkGMwKN6wGh3cRvbzqRtjm-r6rnjCxnobRl";

        //JW: Remove white space from campus names (fcm channels cannot contain spaces)
        $subscriptionNoSpace = $string = str_replace(' ', '', $subscription);
        //JW: notification title text
        $notificationTitle = "There is food available at " . $subscription; 
        //JW: notification body text
        $notificationBody = "Tap for more information";

        //JW: push notification format
        $data = array (
            'to' => "/topics/" . $subscriptionNoSpace,
            'priority' => 'high',
            'notification'=> array(
                'title' => $notificationTitle,
                'body' => $notificationBody,
                'sound' => 'default',
                'icon' => 'push_icon',
            )
        );
        
        $json_message = json_encode($data);

        //JW: open connection using curl
        $ch = curl_init();

        //JW: set url, post method, post fields and http headers
        curl_setopt($ch, CURLOPT_URL, $url); //JW: sets url
        curl_setopt($ch, CURLOPT_POST, true); //JW: sets post method
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //JW: stores firebase result json reply as string instead of directly outputting json via curl_exec (which broke xamarin app as it was freaking out with multiple json replies to deal with)
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_message); //JW: sets json message
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: key=' . $serverKey
        ]); //JW: set the content type and the firebase authorization key for our server in the http header
        
        //JW: execute post via curl
        $result = curl_exec($ch);

        echo $result . " | ";
    }
?>