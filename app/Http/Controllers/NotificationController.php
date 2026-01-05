<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\NotificationComment;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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

        // Return topics that client should subscribe to
        $topics = $this->getUserTopics($user);

        return response()->json([
            'status' => 'success',
            'message' => 'FCM token registered successfully.',
            'topics' => $topics,
        ]);
    }

    /**
     * Get topics that user should subscribe to based on their active subscriptions
     */
    public function getUserTopics(Request $request = null)
    {
        // Handle both direct call with User object and API request
        if ($request instanceof User) {
            $user = $request;
        } else {
            $data = $request->validate([
                'user_id' => 'required|exists:users,id',
            ]);
            $user = User::find($data['user_id']);
        }

        // Get all exam types the user has paid subscriptions for
        $activeTypeIds = $user->subscriptions()
            ->where('payment_status', 'paid')
            ->pluck('type_id')
            ->unique()
            ->values()
            ->toArray();

        $topics = array_map(function($typeId) {
            return FcmService::getTopicName($typeId);
        }, $activeTypeIds);

        if ($request instanceof User) {
            return $topics;
        }

        return response()->json([
            'status' => 'success',
            'topics' => $topics,
            'type_ids' => $activeTypeIds,
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
            'image' => 'sometimes|nullable|image|max:2048',
            'type_id' => 'required|exists:types,id',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $data['image_url'] = $request->file('image')->store('notifications', 'public');
        }

        // Remove 'image' from data array as it's not a database field
        unset($data['image']);

        $notification = AppNotification::create($data);

        $this->dispatchFcm($notification);

        return response()->json([
            'status' => 'success',
            'data' => $notification,
        ], 201);
    }

    public function like(Request $request, AppNotification $notification)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $data['user_id'];

        // Check if user has already reacted
        $existingReaction = DB::table('notification_reactions')
            ->where('app_notification_id', $notification->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingReaction) {
            if ($existingReaction->reaction_type === 'like') {
                // User already liked, return without doing anything
                return response()->json([
                    'status' => 'success',
                    'message' => 'You have already liked this notification.',
                    'data' => $notification->fresh(),
                ], 200);
            } else {
                // User previously disliked, change to like
                DB::table('notification_reactions')
                    ->where('app_notification_id', $notification->id)
                    ->where('user_id', $userId)
                    ->update(['reaction_type' => 'like']);

                // Decrement dislike, increment like
                $notification->decrement('dislike_count');
                $notification->increment('like_count');
            }
        } else {
            // New like
            DB::table('notification_reactions')->insert([
                'app_notification_id' => $notification->id,
                'user_id' => $userId,
                'reaction_type' => 'like',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $notification->increment('like_count');
        }

        return response()->json([
            'status' => 'success',
            'data' => $notification->fresh(),
        ]);
    }

    public function dislike(Request $request, AppNotification $notification)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $data['user_id'];

        // Check if user has already reacted
        $existingReaction = DB::table('notification_reactions')
            ->where('app_notification_id', $notification->id)
            ->where('user_id', $userId)
            ->first();

        if ($existingReaction) {
            if ($existingReaction->reaction_type === 'dislike') {
                // User already disliked, return without doing anything
                return response()->json([
                    'status' => 'success',
                    'message' => 'You have already disliked this notification.',
                    'data' => $notification->fresh(),
                ], 200);
            } else {
                // User previously liked, change to dislike
                DB::table('notification_reactions')
                    ->where('app_notification_id', $notification->id)
                    ->where('user_id', $userId)
                    ->update(['reaction_type' => 'dislike']);

                // Decrement like, increment dislike
                $notification->decrement('like_count');
                $notification->increment('dislike_count');
            }
        } else {
            // New dislike
            DB::table('notification_reactions')->insert([
                'app_notification_id' => $notification->id,
                'user_id' => $userId,
                'reaction_type' => 'dislike',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $notification->increment('dislike_count');
        }

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
        // If notification doesn't have a type_id, don't send (type_id is required)
        if (!$notification->type_id) {
            Log::warning('Notification missing type_id, skipping FCM dispatch', [
                'notification_id' => $notification->id,
            ]);
            return;
        }

        try {
            $fcmService = new FcmService();
            $topic = FcmService::getTopicName($notification->type_id);

            // Prepare data payload
            $data = [
                'id' => (string) $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'image_url' => $notification->image_url ?? '',
                'type_id' => (string) $notification->type_id,
                'like_count' => (string) $notification->like_count,
                'dislike_count' => (string) $notification->dislike_count,
                'comment_count' => (string) $notification->comment_count,
                'created_at' => $notification->created_at->toIso8601String(),
            ];

            // Prepare notification payload (shows as system notification)
            $notificationPayload = [
                'title' => $notification->title,
                'body' => $notification->body,
            ];

            if ($notification->image_url) {
                $notificationPayload['image'] = $notification->image_url;
            }

            // Send to topic
            $success = $fcmService->sendToTopic($topic, $data, $notificationPayload);

            if ($success) {
                Log::info('FCM notification dispatched successfully', [
                    'notification_id' => $notification->id,
                    'topic' => $topic,
                ]);
            } else {
                Log::error('FCM notification dispatch failed', [
                    'notification_id' => $notification->id,
                    'topic' => $topic,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('FCM dispatch exception', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}

