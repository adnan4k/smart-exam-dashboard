<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\YearGroup;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        // Validate the request inputs
        $request->validate([
            'user_id'       => 'required|exists:users,id',
            'year_group_id' => 'required|exists:year_groups,id',
            'image'         => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'amount'        => 'required|integer',
        ]);

        // Handle the image upload and store it in the public storage under the "subscriptions" directory
        $imagePath = $request->file('image')->store('subscriptions', 'public');

        // Retrieve the user and year group or fail
        $user = User::findOrFail($request->user_id);
        $yearGroup = YearGroup::findOrFail($request->year_group_id);

        // Check if a subscription for the given year group already exists and its payment status is 'paid'
        $existingSubscription = $user->subscriptions()->where('year_group_id', $yearGroup->id)->first();

        if ($existingSubscription && $existingSubscription->payment_status === 'paid') {
            // Update the existing subscription with the new details
            $existingSubscription->update([
                'start_date' => now(),
                'end_date'   => now()->addYear(),
                'image'      => $imagePath,
                'amount'     => $request->amount,
            ]);

            return response()->json([
                'message'      => 'Subscription updated successfully.',
                'subscription' => $existingSubscription,
            ], 200);
        }

        // Create a new subscription if none exists meeting the criteria
        $subscription = $user->subscriptions()->create([
            'year_group_id' => $yearGroup->id,
            'start_date'    => now(),
            'end_date'      => now()->addYear(),
            'image'         => $imagePath,
            'amount'        => $request->amount,
        ]);

        return response()->json([
            'message'      => 'Subscription created successfully.',
            'subscription' => $subscription,
        ], 201);
    }

}
