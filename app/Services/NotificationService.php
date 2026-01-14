<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Support\Facades\Http;

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

        Http::withHeaders([
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
