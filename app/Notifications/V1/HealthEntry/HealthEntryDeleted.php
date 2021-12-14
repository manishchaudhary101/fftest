<?php

namespace App\Notifications\V1\HealthEntry;

use Illuminate\Bus\Queueable;
use App\Channels\V1\FcmChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;
class HealthEntryDeleted extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */

    private $healthEntryObj;
  
    public function __construct(\App\Models\HealthEntry $healthEntry)
    {
        $this->healthEntryObj = $healthEntry;
        Log::debug('--------health-entry-deleted-notification--------');
        Log::debug($healthEntry->id);
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

  
    public function toFcm($notifiable)
    {
        $fcmUrl = 'https://fcm.googleapis.com/fcm/send';

        $msg = [
            'healthEntryID' => $this->healthEntryObj->id,
            'notificationType'  => NOTIFICATION_TYPE_TAG_UPDATE,
            'action' => 0
        ];


        $fcmNotification = [
           "content_available" => true,
            "priority" =>5,
            'to' => $notifiable->fcm_deviceToken, //single token
            'data' => $msg,
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

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'healthEntryID' => $this->healthEntryObj->id,
            'healthEntryNote' => $this->healthEntryObj->note,
        ];
    }
}
