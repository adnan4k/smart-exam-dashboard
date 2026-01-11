<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\PasswordResetOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetController extends Controller
{
    /**
     * Request password reset - sends OTP to email
     * 
     * POST /auth/password-reset/request
     * Request: { "email": "user@example.com" }
     * Success: 200 { "message": "otp_sent" }
     */
    public function request(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'No user found with this email address.'
            ], 404);
        }

        // Generate 6-digit OTP
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Delete any existing OTPs for this email
        DB::table('password_reset_otps')
            ->where('email', $validated['email'])
            ->where('used', false)
            ->delete();

        // Store OTP with expiration (10 minutes)
        DB::table('password_reset_otps')->insert([
            'email' => $validated['email'],
            'otp' => $otp,
            'reset_token' => null,
            'expires_at' => Carbon::now()->addMinutes(10),
            'used' => false,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        // Send OTP via email
        $user->notify(new PasswordResetOtp($otp));

        return response()->json([
            'message' => 'otp_sent'
        ], 200);
    }

    /**
     * Verify OTP and return reset token
     * 
     * POST /auth/password-reset/verify
     * Request: { "email": "user@example.com", "otp": "123456" }
     * Success: 200 { "reset_token": "<single-use-token>" }
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|size:6',
        ]);

        // Find valid OTP record
        $otpRecord = DB::table('password_reset_otps')
            ->where('email', $validated['email'])
            ->where('otp', $validated['otp'])
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'message' => 'Invalid or expired OTP.'
            ], 400);
        }

        // Generate single-use reset token
        $resetToken = Str::random(64);

        // Update OTP record with reset token
        DB::table('password_reset_otps')
            ->where('id', $otpRecord->id)
            ->update([
                'reset_token' => $resetToken,
                'updated_at' => Carbon::now(),
            ]);

        return response()->json([
            'reset_token' => $resetToken
        ], 200);
    }

    /**
     * Confirm password reset using reset token
     * 
     * POST /auth/password-reset/confirm
     * Request: { "email": "...", "reset_token": "...", "new_password": "..." }
     * Success: 200 { "message": "password_reset" }
     */
    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
            'reset_token' => 'required|string',
            'new_password' => 'required|string|min:6',
        ]);

        // Find valid reset token
        $otpRecord = DB::table('password_reset_otps')
            ->where('email', $validated['email'])
            ->where('reset_token', $validated['reset_token'])
            ->where('used', false)
            ->where('expires_at', '>', Carbon::now())
            ->whereNotNull('reset_token')
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'message' => 'Invalid or expired reset token.'
            ], 400);
        }

        // Find user
        $user = User::where('email', $validated['email'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.'
            ], 404);
        }

        // Update password
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        // Mark OTP record as used
        DB::table('password_reset_otps')
            ->where('id', $otpRecord->id)
            ->update([
                'used' => true,
                'updated_at' => Carbon::now(),
            ]);

        // Delete all OTP records for this email (cleanup)
        DB::table('password_reset_otps')
            ->where('email', $validated['email'])
            ->delete();

        return response()->json([
            'message' => 'password_reset'
        ], 200);
    }
}
