<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache; // Import Cache facade

class AuthController extends Controller
{
    /**
     * Handle user authentication.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'sometimes|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $deviceName = $request->input('device_name') ?: $request->header('User-Agent') ?: 'Unknown Device';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    /**
     * Log the user out (revoke the token).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        return response()->json($request->user()->load("role.permissions"));
    }

    /**
     * Update the authenticated user's profile.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string', // Base64 string or URL - mapped to 'photo' column
        ]);

        // Update only the fields that are present in the request
        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (array_key_exists('phone', $validated)) {
            $user->phone = $validated['phone'];
        }

        if (array_key_exists('avatar', $validated)) {
            // Map 'avatar' from frontend to 'photo' column in database
            // TODO: If base64, save to storage and update with file path
            // For now, just save the string (could be URL or base64)
            $user->photo = $validated['avatar'];
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $user->load("role.permissions")
        ]);
    }

    /**
     * Send a password reset link to the given user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function forgetPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $response = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Password reset link sent!'])
            : response()->json(['message' => 'Failed to send password reset link.'], 500);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $response = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        return $response == Password::PASSWORD_RESET
            ? response()->json(['message' => 'Password has been reset!'])
            : response()->json(['message' => 'Failed to reset password.'], 500);
    }

    /**
     * Change the authenticated user's password.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The provided password does not match your current password.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
        ])->save();

        return response()->json(['message' => 'Password changed successfully.']);
    }

    /**
     * Request an OTP for unauthenticated user (passwordless login).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function requestOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'device_name' => 'sometimes|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            // Return generic success to prevent user enumeration
            return response()->json(['message' => 'If a matching user is found, an OTP has been sent.']);
        }

        $otp = rand(100000, 999999);
        $otp_key = 'otp_' . $user->id;

        // Store OTP in cache for 5 minutes
        Cache::put($otp_key, $otp, now()->addMinutes(5));

        // TODO: Implement actual email/SMS sending
        // For now, log the OTP for testing
        \Log::info("OTP for {$user->email}: {$otp}");

        return response()->json(['message' => 'If a matching user is found, an OTP has been sent.']);
    }

    /**
     * Verify the OTP provided by the user and log them in.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|string|digits:6',
            'device_name' => 'sometimes|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found.'],
            ]);
        }

        $otp_key = 'otp_' . $user->id;
        $storedOtp = Cache::get($otp_key);

        if (!$storedOtp || $storedOtp != $request->otp) {
            throw ValidationException::withMessages([
                'otp' => ['The provided OTP is invalid or expired.'],
            ]);
        }

        // OTP is valid, clear it from cache
        Cache::forget($otp_key);

        $deviceName = $request->input('device_name') ?: $request->header('User-Agent') ?: 'Unknown Device';
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'OTP verified and user logged in successfully.',
            'access_token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}

