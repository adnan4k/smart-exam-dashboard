<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\Type;
use App\Models\User;
use App\Models\YearGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * List all available access packages for students (with optional subscription status per user).
     *
     * GET /api/packages?user_id=123
     */
    public function packages(Request $request)
    {
        $user = $request->filled('user_id') ? User::find($request->input('user_id')) : null;

        $packages = Package::active()
            ->with(['defaultSubjects'])
            ->ordered()
            ->get()
            ->map(function ($pkg) use ($user) {
                $isSubscribed = false;
                $paymentStatus = null;
                $sub = null;

                if ($user) {
                    if ($user->hasPaidAllAccess()) {
                        $isSubscribed = true;
                        $paymentStatus = 'paid';
                    } else {
                        $sub = $user->subscriptions()
                            ->where('package_id', $pkg->id)
                            ->with('subjects')
                            ->latest()
                            ->first();

                        if ($sub) {
                            $paymentStatus = $sub->payment_status;
                            $isSubscribed = ($sub->payment_status === 'paid');
                        }
                    }
                }

                return [
                    'id' => $pkg->id,
                    'name' => $pkg->name,
                    'slug' => $pkg->slug,
                    'description' => $pkg->description,
                    'price' => (float) $pkg->price,
                    'duration_days' => $pkg->duration_days,
                    'max_subjects' => $pkg->max_subjects ?? 7,
                    'default_subjects' => $pkg->defaultSubjects->map(fn ($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                        'year' => $s->year,
                        'region' => $s->region,
                    ]),
                    'selected_subjects' => $sub ? $sub->subjects->map(fn ($s) => [
                        'id' => $s->id,
                        'name' => $s->name,
                    ]) : [],
                    'remaining_subject_slots' => $sub ? $sub->remainingSubjectSlots() : ($pkg->max_subjects ?? 7),
                    'is_subscribed' => $isSubscribed,
                    'payment_status' => $paymentStatus,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $packages,
        ]);
    }

    /**
     * Submit a subscription payment proof (bank slip) for a package or exam type.
     *
     * POST /api/subscribe
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'image'       => 'required',
            'package_id'  => 'nullable|exists:packages,id',
            'subject_ids' => 'nullable|array',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        // Handle receipt image upload
        $imagePath = '';
        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $imagePath = $request->file('image')->store('subscriptions', 'public');
        }

        $user = User::findOrFail($request->user_id);

        // Path A: Subscribing to a specific Package (1st Sem, 2nd Sem, COC, All Access)
        if ($request->filled('package_id')) {
            $package = Package::findOrFail($request->package_id);
            $maxAllowed = $package->max_subjects ?: 7;

            // Optional custom subject selection validation
            $subjectIds = null;
            if ($request->has('subject_ids')) {
                $subjectIds = array_values(array_unique((array) $request->input('subject_ids', [])));
                if (count($subjectIds) > $maxAllowed) {
                    return response()->json([
                        'message' => "Package '{$package->name}' allows a maximum of {$maxAllowed} subjects. You provided " . count($subjectIds) . ".",
                    ], 422);
                }
            }

            $existing = $user->subscriptions()
                ->where('package_id', $package->id)
                ->first();

            if ($existing) {
                if ($existing->payment_status === 'paid') {
                    return response()->json([
                        'message' => "User already has an active subscription for package '{$package->name}'.",
                        'subscription' => $existing,
                    ], 400);
                }

                if (in_array($existing->payment_status, ['pending', 'failed'])) {
                    $existing->update([
                        'start_date' => now(),
                        'end_date' => now()->addDays($package->duration_days ?: 365),
                        'image' => $imagePath,
                        'amount' => $package->price,
                        'payment_status' => 'pending',
                    ]);

                    if ($subjectIds !== null && count($subjectIds) > 0) {
                        $existing->subjects()->sync($subjectIds);
                    } else {
                        $existing->syncDefaultSubjects();
                    }

                    return response()->json([
                        'message' => 'Subscription payment proof resubmitted successfully.',
                        'subscription' => $existing->load('subjects'),
                        'package' => $package,
                    ], 200);
                }
            }

            $subscription = $user->subscriptions()->create([
                'package_id' => $package->id,
                'type_id' => $user->type_id,
                'start_date' => now(),
                'end_date' => now()->addDays($package->duration_days ?: 365),
                'image' => $imagePath,
                'amount' => $package->price,
                'payment_status' => 'pending',
            ]);

            if ($subjectIds !== null && count($subjectIds) > 0) {
                $subscription->subjects()->sync($subjectIds);
            } else {
                $subscription->syncDefaultSubjects();
            }

            return response()->json([
                'message' => 'Subscription created successfully. Awaiting verification.',
                'subscription' => $subscription->load('subjects'),
                'package' => $package,
            ], 201);
        }

        // Path B: Legacy subscription by Type ID (backward compatibility)
        if (!$user->type_id) {
            return response()->json([
                'message' => 'User has no exam type associated.',
            ], 400);
        }

        $type = Type::findOrFail($user->type_id);
        $existingSubscription = $user->subscriptions()
            ->where('type_id', $user->type_id)
            ->whereNull('package_id')
            ->first();

        if ($existingSubscription) {
            if ($existingSubscription->payment_status === 'paid') {
                return response()->json([
                    'message' => 'User already has an active subscription.',
                    'subscription' => $existingSubscription,
                ], 400);
            }

            if (in_array($existingSubscription->payment_status, ['pending', 'failed'])) {
                $existingSubscription->update([
                    'start_date' => now(),
                    'end_date' => now()->addYear(),
                    'image' => $imagePath,
                    'amount' => $type->price,
                    'payment_status' => 'pending',
                ]);

                return response()->json([
                    'message' => 'Subscription resubmitted successfully.',
                    'subscription' => $existingSubscription,
                ], 200);
            }
        }

        $subscription = $user->subscriptions()->create([
            'type_id' => $user->type_id,
            'start_date' => now(),
            'end_date' => now()->addYear(),
            'image' => $imagePath,
            'amount' => $type->price,
            'payment_status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Subscription created successfully.',
            'subscription' => $subscription,
        ], 201);
    }

    /**
     * Check subscription status for a user, returning package access breakdown.
     *
     * POST /api/check-subscription
     */
    public function checkSubscription(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($request->user_id);

        $hasAllAccess = $user->hasPaidAllAccess();
        $paidPackageSlugs = $user->paidPackageSlugs();
        $hasPaidPackage = $user->hasPaidPackage();

        $activeSubscriptions = $user->subscriptions()
            ->where('payment_status', 'paid')
            ->with(['package', 'subjects'])
            ->get();

        $subscribedPackages = $activeSubscriptions
            ->filter(fn ($s) => $s->package !== null)
            ->map(fn ($s) => [
                'id' => $s->package->id,
                'name' => $s->package->name,
                'slug' => $s->package->slug,
                'max_subjects' => $s->package->max_subjects ?? 7,
                'start_date' => $s->start_date,
                'end_date' => $s->end_date,
                'selected_subjects' => $s->subjects->map(fn ($sub) => [
                    'id' => $sub->id,
                    'name' => $sub->name,
                ]),
                'remaining_slots' => $s->remainingSubjectSlots(),
            ])
            ->values();

        $latestSub = $user->subscriptions()->latest()->first();

        return response()->json([
            'status' => $hasPaidPackage ? 'paid' : ($latestSub ? $latestSub->payment_status : 'none'),
            'is_subscribed' => $hasPaidPackage,
            'has_all_access' => $hasAllAccess,
            'subscribed_package_slugs' => $paidPackageSlugs,
            'subscribed_packages' => $subscribedPackages,
            'user_id' => $user->id,
            'type_id' => $user->type_id,
            'type_price' => $user->type_id && ($type = Type::find($user->type_id)) ? $type->price : null,
            'message' => $hasPaidPackage ? 'Active subscription found.' : 'No active subscription found.',
        ], 200);
    }

    /**
     * Choose or update subjects for a user's package subscription (up to max_subjects).
     *
     * POST /api/subscriptions/select-subjects
     */
    public function selectSubjects(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
        ]);

        $user = User::findOrFail($request->user_id);
        $package = Package::findOrFail($request->package_id);

        $subscription = $user->subscriptions()
            ->where('package_id', $package->id)
            ->whereIn('payment_status', ['paid', 'pending'])
            ->latest()
            ->first();

        if (! $subscription) {
            return response()->json([
                'status' => 'error',
                'message' => "No active or pending subscription found for package '{$package->name}'. Please subscribe first.",
            ], 404);
        }

        $maxAllowed = $package->max_subjects ?: 7;
        $uniqueSubjectIds = array_values(array_unique($request->subject_ids));

        if (count($uniqueSubjectIds) > $maxAllowed) {
            return response()->json([
                'status' => 'error',
                'message' => "Package '{$package->name}' allows a maximum of {$maxAllowed} subjects. You selected " . count($uniqueSubjectIds) . ".",
            ], 422);
        }

        $subscription->subjects()->sync($uniqueSubjectIds);

        return response()->json([
            'status' => 'success',
            'message' => 'Subjects updated successfully.',
            'max_subjects' => $maxAllowed,
            'selected_count' => count($uniqueSubjectIds),
            'remaining_slots' => max(0, $maxAllowed - count($uniqueSubjectIds)),
            'selected_subjects' => $subscription->subjects()->get(['subjects.id', 'subjects.name', 'subjects.year']),
        ], 200);
    }
}
