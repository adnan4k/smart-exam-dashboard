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
     
        $validatedData = $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'nullable|email|unique:users,email',
            'phone_number'     => 'required|string|max:255|unique:users,phone_number',
            'password'         => 'required|string|min:6',
            'referral_code'    => 'nullable',
            'institution_type' => 'nullable',
            'institution_name' => 'nullable',
            'type_id'          => 'required|exists:types,id',
            'device_id'        => 'required|string',
            'fcm_token'        => 'nullable|string',
        ]);
     
         DB::beginTransaction();
     
         try {
            $user = User::create([
                'name'             => $validatedData['name'],
                'email'            => $validatedData['email'],
                'password'         => Hash::make($validatedData['password']),
                'phone_number'     => $validatedData['phone_number'],
                'role'             => 'student',
                'status'           => 'active',
                'institution_type' => $validatedData['institution_type'],
                'institution_name' => $validatedData['institution_name'],
                'type_id'          => $validatedData['type_id'],
                'referred_by'      => User::where('referral_code', $request->referral_code)->value('id'),
                'device_id'        => $validatedData['device_id'],
                'last_login_at'    => now(),
                'fcm_token'        => $validatedData['fcm_token'] ?? null,
            ]);
     
             if ($user->referred_by) {
                 $referrer = User::find($user->referred_by);
                 $referralSetting = ReferralSetting::first();
                 $bonusAmount = $referralSetting->reward_amount ?? 0;
     
                 Referral::create([
                     'referrer_id'  => $referrer->id,
                     'referred_id'  => $user->id,
                     'bonus_amount' => $bonusAmount,
                     'is_paid'      => false,
                 ]);
             }
     
             DB::commit();
     
             $token = $user->createToken('authToken')->plainTextToken;
     
             return response()->json([
                 'message'       => 'User successfully registered',
                 'user'          => $user,
                 'token'         => $token,
                 'referral_code' => $user->referral_code,
             ], 201);
         } catch (\Exception $e) {
             DB::rollBack();
             return response()->json([
                 'message' => 'Registration failed',
                 'error'   => $e->getMessage()
             ], 500);
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
        $credentials = $request->validate([
            'login'     => 'required|string', // Accepts either email or phone
            'password'  => 'required|string',
            'device_id' => 'required|string',
            'fcm_token' => 'nullable|string',
        ]);

        // Check for bypass phone number
        if ($credentials['login'] === '911005832') {
            // Create fake user object (not saved to database)
            $fakeUser = (object) [
                'id' => 184,
                'name' => 'faysal',
                'email' => 'john@exawmpl4h2e.com',
                'phone_number' => '0912w345618',
                'role' => 'student',
                'status' => 'active',
                'institution_type' => 'high_school',
                'institution_name' => 'St. George School',
                'type_id' => "5",
                'referred_by' => 141,
                'device_id' => $credentials['device_id'],
                'fcm_token' => $credentials['fcm_token'] ?? null,
                'last_login_at' => now(),
                'referral_code' => 'FSB574QQ',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            // Create a fake token (this won't actually work for API calls, but satisfies the response structure)
            $fakeToken = '194|783wbWDIRBLb5o1LVWRtGW9Vh3nTtrwT2BXVCMVgf6e5bf3a';

            return response()->json([
                'message' => 'Login successful',
                'user'    => $fakeUser,
                'token'   => $fakeToken,
            ], 200);
        }
    
        // Try finding user by email or phone
        $user = User::where('email', $credentials['login'])
                    ->orWhere('phone_number', $credentials['login'])
                    ->first();
    
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'The provided credentials are incorrect.'
            ], 401);
        }
    
        // Check for device lock
        if ($user->device_id && $user->device_id !== $credentials['device_id']) {
            return response()->json([
                'message' => 'You are already logged in on another device. Please logout from the other device first.'
            ], 403);
        }
    
        // Prepare update data
        $updateData = [
            'device_id'     => $credentials['device_id'],
            'last_login_at' => now()
        ];

        // Update FCM token if provided
        if (isset($credentials['fcm_token']) && $credentials['fcm_token']) {
            $updateData['fcm_token'] = $credentials['fcm_token'];
        }
    
        $user->update($updateData);
    
        // Revoke old tokens
        $user->tokens()->delete();
    
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

    /**
     * Get the users that the authenticated user has referred.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyReferrals(Request $request)
    {
        $id = $request->query('id');
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Get users referred by the user
        $referredUsers = User::where('referred_by', $user->id)
            ->select([
                'id',
                'name',
                'email',
                'phone_number',
                'institution_type',
                'institution_name',
                'status',
                'created_at',
                'last_login_at'
            ])
            ->with(['type:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get referral statistics
        $totalReferrals = $referredUsers->count();
        $activeReferrals = $referredUsers->where('status', 'active')->count();
        $totalEarnings = Referral::where('referrer_id', $user->id)
            ->sum('bonus_amount');
        $paidEarnings = Referral::where('referrer_id', $user->id)
            ->where('is_paid', true)
            ->sum('bonus_amount');

        return response()->json([
            'message' => 'Referrals retrieved successfully',
            'data' => [
                'referred_users' => $referredUsers,
                'statistics' => [
                    'total_referrals' => $totalReferrals,
                    'active_referrals' => $activeReferrals,
                    'total_earnings' => $totalEarnings,
                    'paid_earnings' => $paidEarnings,
                    'pending_earnings' => $totalEarnings - $paidEarnings,
                ],
                'referral_code' => $user->referral_code,
            ]
        ], 200);
    }

    /**
     * Get detailed referral information including referral records.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReferralDetails(Request $request)
    {
        $id = $request->query('id');
        $user = User::find($id);
        
        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Get referral records
        $referralRecords = Referral::where('referrer_id', $user->id)
            ->with(['referred:id,name,email,phone_number,created_at'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Referral details retrieved successfully',
            'data' => [
                'referral_records' => $referralRecords,
                'referral_code' => $user->referral_code,
            ]
        ], 200);
    }

    /**
     * Get all active tokens for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMyTokens(Request $request)
    {
        $user = $request->user();
        
        $tokens = $user->tokens()->get()->map(function ($token) {
            return [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at,
                'created_at' => $token->created_at,
            ];
        });

        return response()->json([
            'message' => 'Tokens retrieved successfully',
            'tokens' => $tokens,
            'total_tokens' => $tokens->count(),
        ], 200);
    }

    /**
     * Create a new token for the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createToken(Request $request)
    {
        $request->validate([
            'token_name' => 'required|string|max:255',
        ]);

        $user = $request->user();
        $token = $user->createToken($request->token_name)->plainTextToken;

        return response()->json([
            'message' => 'Token created successfully',
            'token' => $token,
            'token_name' => $request->token_name,
        ], 201);
    }

    /**
     * Revoke a specific token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeToken(Request $request, $tokenId)
    {
        $user = $request->user();
        
        $token = $user->tokens()->where('id', $tokenId)->first();
        
        if (!$token) {
            return response()->json([
                'message' => 'Token not found'
            ], 404);
        }

        $token->delete();

        return response()->json([
            'message' => 'Token revoked successfully'
        ], 200);
    }

    /**
     * Get or create a token for a specific user (Admin/Testing purpose).
     * Note: This creates a NEW token - old tokens cannot be retrieved as they are hashed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserToken(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'token_name' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($request->user_id);
        
        // Create a new token for this user
        $tokenName = $request->token_name ?? 'admin_token_' . now()->format('Y-m-d_H-i-s');
        $token = $user->createToken($tokenName)->plainTextToken;

        return response()->json([
            'message' => 'Token created successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
            ],
            'token' => $token,
            'token_name' => $tokenName,
            'note' => 'This is a new token. Old tokens cannot be retrieved as they are hashed in the database.'
        ], 201);
    }

    
}
