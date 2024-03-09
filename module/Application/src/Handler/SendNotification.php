<?php
namespace Application\Handler;


class SendNotification
{
    const FCM_KEY='AIzaSyAUgWUYDr5fHErbmnu2lh9nnReRs1YeuM8';
    public function __construct(){
    }
    public function sendPushNotificationToFCMSever()
    {

        $path_to_gmc_server = 'https://fcm.googleapis.com/fcm/send';

       /* $fields = array(
            'registration_ids' => $registation_ids,
            'data' => $message,
            "content_available" => true,
            'badge'=>"10",
            'priority' => "high",
            'notification' => array(
                "body" => $message["text"],
                "title" => "New Notification",
            ),
        );*/

        $fields = array(
           // 'registration_ids' => array('dXi8dt0Iw0g:APA91bHJKbMpKmix0ODwwGyeQo1yIjxO9J52Nau35PZPL0lypvjTIH8pmhNCWoS7T1MoHeGcOXtTWNCpY_j-_0LP3eGMWaROCDldE_Sn38fUIenw2L58C0O3pwr0khO8WPSzuONQ4VNg'),
            //'to'            => "/topics/delete",
            "priority" => "high",
            'data' => array('title'=>'delete the whole data'),
            "content_available" => true,
            "alert" => array(
                "body" => "delete the whole data",
                "title" => "delete"
            ),
           "condition" =>  "'delete' in topics",
          /*  "notification" => array(
                "sound" => "default",
                "body" => "delete the files",
                "title" => "delete the data"
            ),*/
        );

        $headers = array(
            /*'Authorization: key=AIzaSyACHuw80__3tKm8IySJNPfKVjgy73zfDd8',*/
            'Authorization: key='.\Application\Handler\SendNotification::FCM_KEY,
            'Content-Type: application/json'
        );

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $path_to_gmc_server);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));


        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }
}