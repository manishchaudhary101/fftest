<?php

namespace App\Notifications\V1\Workout;

use App\Channels\V1\FcmChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class WorkoutEdited extends Notification
{
    use Queueable;

    /***
     * @var \App\Models\FB_Workout
     */
    private $workoutObj;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(\App\Models\FB_Workout $workout)
    {
        $this->workoutObj = $workout;
        Log::debug('--------workout-edited-notification--------');
        Log::debug($workout->id);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database',FcmChannel::class];
    }


    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'workoutID' => $this->workoutObj->id,
            'workoutTitle' => $this->workoutObj->title,
            'workoutActivityType' => $this->workoutObj->activity_type,
        ];
    }

    /***
     * @param mixed $notifiable
     */
    public function toFcm($notifiable)
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

        $msg = [
            'workoutID' => $this->workoutObj->id,
            'notificationType'  => NOTIFICATION_TYPE_WORKOUT_UPDATE,
            'action' => 1,
            'newTitle'  => $this->workoutObj->title,
            'newActivityType'  => $this->workoutObj->activity_type,
        ];


        $fcmNotification = [
//            'registration_ids' => $userQuery, //multiple token array
            "content_available" => true,
	        "priority" =>5,
            'to' => $notifiable->fcm_deviceToken, //single token
            'data' => $msg,
//            'data' => $this->workoutObj->toJson()
        ];

        $headers = [
            'Authorization: key=' . env('FCM_SERVER_KEY'),
            'Content-Type: application/json'
        ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $fcmUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmNotification));
        $result = curl_exec($ch);
        curl_close($ch);

        Log::debug($result);
        return $result;
    }
}
