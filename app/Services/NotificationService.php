<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FirebaseNotification;

class NotificationService
{
    public function sendToUser($user, string $title, string $body, array $data = [])
    {
        Notification::create([
            'user_id' => $user->id,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
        ]);

        if (!$user->fcm_token) {
            return;
        }


        // this uses CloudMessage and kreait/laravel-firebase
        try {
            $messaging = app('firebase.messaging');

            $message = CloudMessage::withTarget('token', $user->fcm_token)
                ->withNotification(
                    FirebaseNotification::create($title, $body)
                )
                ->withData($data);

            $messaging->send($message);

        } catch (\Throwable $e) {
            Log::error('FCM Send Failed', [
                'user_id' => $user->id,
                'error'   => $e->getMessage(),
            ]);
        }

        // try{
        //     $response = Http::withHeaders([
        //         'Authorization' => 'key=' . config('services.fcm.server_key'),
        //         'Content-Type'  => 'application/json',
        //     ])->post('https://fcm.googleapis.com/fcm/send', [
        //         'to' => $user->fcm_token,
        //         'notification' => [
        //             'title' => $title,
        //             'body'  => $body,
        //         ],
        //         'data' => $data,
        //     ]);
        //     if ($response->failed()) {
        //         Log::error('FCM Notification Failed: ' . $response->body());
        //     }
        // }
        // catch(\Exception $e){
        //     //log the error
        //     Log::error('FCM Notification Error: ' . $e->getMessage());
        // }

        // Http::withToken(config('services.fcm.server_key'))
        //     ->post('https://fcm.googleapis.com/fcm/send', [
        //         'to' => $user->fcm_token,
        //         'notification' => [
        //             'title' => $title,
        //             'body' => $body,
        //         ],
        //         'data' => $data,
        //     ]);
    }
}
