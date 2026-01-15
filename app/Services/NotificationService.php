<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        try{
            $response = Http::withHeaders([
                'Authorization' => 'key=' . config('services.fcm.server_key'),
                'Content-Type'  => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', [
                'to' => $user->fcm_token,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                ],
                'data' => $data,
            ]);
            if ($response->failed()) {
                Log::error('FCM Notification Failed: ' . $response->body());
            }
        }
        catch(\Exception $e){
            //log the error
            Log::error('FCM Notification Error: ' . $e->getMessage());
        }

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
