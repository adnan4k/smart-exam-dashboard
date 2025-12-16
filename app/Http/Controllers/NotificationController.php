<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\NotificationComment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;

class NotificationController extends Controller
{
    public function registerToken(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'fcm_token' => 'required|string',
        ]);

        $user = User::find($data['user_id']);
        $user->update(['fcm_token' => $data['fcm_token']]);

        return response()->json([
            'status' => 'success',
            'message' => 'FCM token registered.',
        ]);
    }

    public function index()
    {
        $notifications = AppNotification::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $notifications,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'image_url' => 'sometimes|nullable|url',
        ]);

        $notification = AppNotification::create($data);

        $this->dispatchFcm($notification);

        return response()->json([
            'status' => 'success',
            'data' => $notification,
        ], 201);
    }

    public function like(AppNotification $notification)
    {
        $notification->increment('like_count');

        return response()->json([
            'status' => 'success',
            'data' => $notification->fresh(),
        ]);
    }

    public function dislike(AppNotification $notification)
    {
        $notification->increment('dislike_count');

        return response()->json([
            'status' => 'success',
            'data' => $notification->fresh(),
        ]);
    }

    public function comment(Request $request, AppNotification $notification)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'comment' => 'required|string',
        ]);

        NotificationComment::create([
            'app_notification_id' => $notification->id,
            'user_id' => $data['user_id'],
            'comment' => $data['comment'],
        ]);

        $notification->increment('comment_count');

        return response()->json([
            'status' => 'success',
            'data' => $notification->fresh()->load('comments.user'),
        ]);
    }

    private function dispatchFcm(AppNotification $notification): void
    {
        $serverKey = Config::get('services.fcm.server_key');
        if (!$serverKey) {
            return;
        }

        $tokens = User::whereNotNull('fcm_token')
            ->where('fcm_token', '!=', '')
            ->pluck('fcm_token')
            ->all();

        if (empty($tokens)) {
            return;
        }

        $payload = [
            'registration_ids' => $tokens,
            'data' => [
                'id' => $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'image_url' => $notification->image_url,
                'like_count' => $notification->like_count,
                'dislike_count' => $notification->dislike_count,
                'comment_count' => $notification->comment_count,
                'created_at' => $notification->created_at->toIso8601String(),
            ],
        ];

        Http::withHeaders([
            'Authorization' => 'key=' . $serverKey,
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/fcm/send', $payload);
    }
}

