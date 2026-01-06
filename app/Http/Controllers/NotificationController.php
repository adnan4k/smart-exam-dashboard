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

        // Get the user's topic (single subscription)
        $topicData = $this->getUserTopic($user);

        return response()->json([
            'status' => 'success',
            'message' => 'FCM token registered successfully.',
            'topic' => $topicData['topic'],
            'type_id' => $topicData['type_id'],
        ]);
    }

    /**
     * Get topic that user should subscribe to based on their active subscription
     * Users only have one exam type subscription
     */
    public function getUserTopic(Request $request = null)
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

        // Get the user's active paid subscription (only one exam type per user)
        $subscription = $user->subscriptions()
            ->where('payment_status', 'paid')
            ->first();

        if (!$subscription) {
            if ($request instanceof User) {
                return ['topic' => null, 'type_id' => null];
            }

            return response()->json([
                'status' => 'success',
                'topic' => null,
                'type_id' => null,
                'message' => 'No active subscription found.',
            ]);
        }

        $typeId = $subscription->type_id;
        $topic = FcmService::getTopicName($typeId);

        if ($request instanceof User) {
            return ['topic' => $topic, 'type_id' => $typeId];
        }

        return response()->json([
            'status' => 'success',
            'topic' => $topic,
            'type_id' => $typeId,
        ]);
    }

    public function index(Request $request)
    {
        $notifications = AppNotification::orderBy('created_at', 'desc')->get();

        // If user_id is provided, include read status for each notification
        if ($request->has('user_id')) {
            $userId = $request->input('user_id');
            
            // Get all read notification IDs for this user
            $readNotificationIds = DB::table('notification_reads')
                ->where('user_id', $userId)
                ->pluck('app_notification_id')
                ->toArray();
            
            // Add is_read property to each notification
            $notifications = $notifications->map(function ($notification) use ($readNotificationIds) {
                $notification->is_read = in_array($notification->id, $readNotificationIds);
                return $notification;
            });
        }

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

    public function markAllAsRead(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'type_id' => 'sometimes|nullable|exists:types,id',
        ]);

        $userId = $data['user_id'];
        $typeId = $data['type_id'] ?? null;

        $inserted = 0;
        $now = now();

        $query = AppNotification::query();

        if ($typeId) {
            $query->where('type_id', $typeId);
        }

        // Chunk to avoid loading all notifications into memory for large datasets
        $query->orderBy('id')->chunkById(500, function ($chunk) use ($userId, $now, &$inserted) {
            $ids = $chunk->pluck('id');

            // Skip notifications already marked as read by this user
            $alreadyRead = DB::table('notification_reads')
                ->where('user_id', $userId)
                ->whereIn('app_notification_id', $ids)
                ->pluck('app_notification_id')
                ->all();

            $toInsert = $ids->diff($alreadyRead);

            if ($toInsert->isEmpty()) {
                return;
            }

            $rows = [];

            foreach ($toInsert as $id) {
                $rows[] = [
                    'app_notification_id' => $id,
                    'user_id' => $userId,
                    'read_at' => $now,
                ];
            }

            DB::table('notification_reads')->insertOrIgnore($rows);

            $inserted += count($toInsert);
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Notifications marked as read.',
            'data' => [
                'marked_count' => $inserted,
                'type_id' => $typeId,
            ],
        ]);
    }

    public function markAsRead(Request $request, AppNotification $notification)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $data['user_id'];

        // Use insert ignore pattern to avoid duplicates (unique constraint on table)
        DB::table('notification_reads')->updateOrInsert(
            [
                'app_notification_id' => $notification->id,
                'user_id' => $userId,
            ],
            [
                'read_at' => now(),
            ]
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Notification marked as read.',
            'data' => [
                'notification_id' => $notification->id,
                'is_read' => true,
            ],
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

