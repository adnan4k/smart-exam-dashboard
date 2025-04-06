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
            'email'         => 'nullable',
            // 'password'      => 'required',
            'phone_number'  => 'required|string|max:255',
            'referral_code' => 'nullable',
            'institution_type' => 'nullable',
            'institution_name' => 'nullable',
            'type_id'=> 'required|exists:types,id', // Added type_id validation
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
                'type_id'       => $request->type_id, // Added type_id
                'referred_by'   => User::where('referral_code', $request->referral_code)->value('id'),
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
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Retrieve the user by email
        $user = User::where('email', $credentials['email'])->first();

        // Check if user exists and the password is correct
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }

        // If using token-based authentication, generate a token for the user
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'user'    => $user,
            'token'   => $token,
        ], 200);
    }
}
