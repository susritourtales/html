<?php
namespace Application\Handler;


class SendFcmNotification
{


    const FCM_KEY='AAAAwHjoHy8:APA91bHIdMFVZeFSMGObIdQLxlNL7a2GMJ0m9bK2cDeL8onjnQo_4fFfIMcDxeKZYpO3mF5TkE3_Wrj6RPH0zFU5DalM62VNwfUeCXSDIi5q4MrkndQ6sD7aEnfcWKlSQ_ujDilm1xAb';
    public function __construct(){

    }


    public function sendPushNotificationToFCMSever($registrationIds, $message = array()){

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
            'registration_ids' => $registrationIds,
            "priority" => "high",
            'data' => $message,
            "content_available" => true,
            "alert" => array(
                "body" => $message["message"],
                "title" => $message["title"]
            )
        );

        $headers = array(
            /*'Authorization: key=AIzaSyACHuw80__3tKm8IySJNPfKVjgy73zfDd8',*/
            'Authorization: key='.\Application\Handler\SendFcmNotification::FCM_KEY,
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