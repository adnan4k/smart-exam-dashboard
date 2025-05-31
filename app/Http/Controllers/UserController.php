<?php

namespace App\Http\Controllers;

use App\Models\Referral;
use App\Models\ReferralSetting;
use App\Models\User; // Make sure this path matches your User model location
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */


    public function register(Request $request)
    {
        Log::info($request->all());
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'nullable|email|unique:users,email',
            'phone_number'  => 'required|string|max:255',
            'referral_code' => 'nullable',
            'institution_type' => 'nullable',
            'institution_name' => 'nullable',
            'type_id'      => 'required|exists:types,id',
            'device_id'    => 'required|string', // Add device_id validation
        ]);

        DB::beginTransaction();

        try {
            // Create the user
            $user = User::create([
                'name'          => $validatedData['name'],
                'email'         => $validatedData['email'],
                'password'      => Hash::make('password'),
                'phone_number'  => $validatedData['phone_number'],
                'role'          => 'student',
                'status'        => 'active',
                'institution_type' => $validatedData['institution_type'],
                'institution_name' => $validatedData['institution_name'],
                'type_id'       => $request->type_id,
                'referred_by'   => User::where('referral_code', $request->referral_code)->value('id'),
                'device_id'     => $validatedData['device_id'],
                'last_login_at' => now(),
            ]);

            // If the user was referred, create a referral record
            if ($user->referred_by) {
                $referrer = User::find($user->referred_by);

                // Get referral reward settings
                $referralSetting = ReferralSetting::first();
                $bonusAmount = $referralSetting->reward_amount ?? 0;

                Referral::create([
                    'referrer_id'  => $referrer->id,
                    'referred_id'  => $user->id,
                    'bonus_amount' => $bonusAmount,
                    'is_paid'      => false,
                ]);
            }
            Log::info($user);
            DB::commit();

            // Generate token if using Laravel Sanctum
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'message' => 'User successfully registered',
                'user'    => $user,
                'token'   => $token,
                'referral_code' => $user->referral_code,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Registration failed', 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate the request data
        $credentials = $request->validate([
            'email'     => 'required|email',
            'password'  => 'required|string',
            'device_id' => 'required|string', // Add device_id validation
        ]);

        // Retrieve the user by email
        $user = User::where('email', $credentials['email'])->first();

        // Check if user exists and the password is correct
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // Check if user is already logged in on another device
        if ($user->device_id && $user->device_id !== $credentials['device_id']) {
            return response()->json([
                'message' => 'You are already logged in on another device. Please logout from the other device first.'
            ], 403);
        }

        // Update user's device_id and last_login_at
        $user->update([
            'device_id' => $credentials['device_id'],
            'last_login_at' => now()
        ]);

        // Revoke all existing tokens
        $user->tokens()->delete();

        // Generate new token
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user'    => $user,
            'token'   => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();
        
        // Clear the device_id
        $user->update([
            'device_id' => null,
            'last_login_at' => null
        ]);

        // Revoke all tokens
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ], 200);
    }
}
