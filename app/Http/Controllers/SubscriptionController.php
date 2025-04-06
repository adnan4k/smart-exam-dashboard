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
        Log::info($request->all());
        // Validate only user_id and image
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
            ->where('payment_status', 'paid')
            ->first();

        if ($existingSubscription) {
            // Update the existing subscription
            $existingSubscription->update([
                'start_date' => now(),
                'end_date' => now()->addYears(3),
                'image' => $imagePath,
                'amount' => $type->price, // Use price from separately queried type
            ]);

            return response()->json([
                'message' => 'Subscription updated successfully.',
                'subscription' => $existingSubscription,
            ], 200);
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
}
