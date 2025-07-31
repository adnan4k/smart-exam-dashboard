<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\YearGroup;
use App\Models\Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'image'   => 'required',
        ]);

        // Handle the image upload
        $imagePath = '';
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('subscriptions', 'public');
        }

        // Retrieve the user
        $user = User::findOrFail($request->user_id);

        if (!$user->type_id) {
            return response()->json([
                'message' => 'User has no exam type associated.',
            ], 400);
        }

        // Get the type and its price
        $type = Type::findOrFail($user->type_id);
        // Check if a subscription already exists and is paid
        $existingSubscription = $user->subscriptions()
            ->where('type_id', $user->type_id)
            ->first();

        if ($existingSubscription) {
            // Check if the existing subscription is already paid
            if ($existingSubscription->payment_status === 'paid') {
                return response()->json([
                    'message' => 'User already has an active subscription.',
                    'subscription' => $existingSubscription,
                ], 400);
            }

            // Allow resubmission for pending or failed subscriptions
            if (in_array($existingSubscription->payment_status, ['pending', 'failed'])) {
                $existingSubscription->update([
                    'start_date' => now(),
                    'end_date' => now()->addYear(),
                    'image' => $imagePath,
                    'amount' => $type->price,
                    'payment_status' => 'pending', // Reset to pending on resubmission
                    'failure_reason' => null, // Clear any previous failure reason
                ]);

                return response()->json([
                    'message' => 'Subscription resubmitted successfully.',
                    'subscription' => $existingSubscription,
                ], 200);
            }
        }

        // Create a new subscription
        $subscription = $user->subscriptions()->create([
            'type_id' => $user->type_id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'image' => $imagePath,
            'amount' => $type->price, // Use price from separately queried type
            'payment_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Subscription created successfully.',
            'subscription' => $subscription,
        ], 201);
    }

    /**
     * Check subscription status for a user
     */
    public function checkSubscription(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $response = [
            'status' => $user->subscriptions()
                ->where('type_id', $user->type_id)
                ->value('payment_status'),
            'message' => '',
            'user_id' => $user->id,
            'type_id' => $user->type_id,
            'type_price' => $user->type_id ? Type::find($user->type_id)->price : null,
        ];

        if (!$user->type_id) {
            $response['message'] = 'No exam type associated with this user.';
            return response()->json($response, 400);
        }

        $subscription = $user->subscriptions()
            ->where('type_id', $user->type_id)
            ->where('payment_status', 'paid')
            ->first();

        if (!$subscription) {
            $response['message'] = 'No active subscription found.';
            return response()->json($response, 200);
        }

        // Successful response with subscription details
        return response()->json([
            'status' => $subscription->payment_status,
            'message' => 'Active subscription found.',
            'user_id' => $user->id,
            'type_id' => $subscription->type_id,
            'type_price' => Type::find($user->type_id)->price,

        ], 200);
    }
}
