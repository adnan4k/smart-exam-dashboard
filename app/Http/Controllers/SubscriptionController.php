<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\YearGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        Log::info($request->all());
        // Validate the request inputs
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type_id' => 'required|exists:types,id',
            'image'   => 'required',
            'amount'  => 'required',
        ]);

        // Handle the image upload
        $imagePath = '';
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('subscriptions', 'public');
        }

        // Retrieve the user or fail
        $user = User::findOrFail($request->user_id);

        // Check if a subscription for the given type already exists and is paid
        $existingSubscription = $user->subscriptions()
            ->where('type_id', $request->type_id)
            ->where('payment_status', 'paid')
            ->first();

        if ($existingSubscription) {
            // Update the existing subscription
            $existingSubscription->update([
                'start_date' => now(),
                'end_date' => now()->addYears(3),
                'image'      => $imagePath,
                'amount'     => $request->amount,
            ]);

            return response()->json([
                'message'      => 'Subscription updated successfully.',
                'subscription' => $existingSubscription,
            ], 200);
        }

        // Create a new subscription
        $subscription = $user->subscriptions()->create([
            'type_id'    => $request->type_id,
            'start_date' => now(),
            'end_date'   => now()->addYear(),
            'image'      => $imagePath,
            'amount'     => $request->amount,
            'payment_status' => 'pending',
        ]);

        return response()->json([
            'message'      => 'Subscription created successfully.',
            'subscription' => $subscription,
        ], 201);
    }
}
