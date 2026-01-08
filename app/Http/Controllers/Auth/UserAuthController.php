<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordChangedNotification;
use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use App\Models\Verification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserAuthController extends Controller
{
    // -------------------- REGISTER --------------------

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'firstname' => 'required|string|max:255',
            'lastname'  => 'required|string|max:255',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'firstname' => $request->firstname,
            'lastname'  => $request->lastname,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'verified'  => false,
            'role'      => "vendor",
        ]);

        $code = Str::upper(Str::random(6));

        Verification::create([
            'user_id'    => $user->id,
            'type'       => 'email',
            'code'       => $code,
            'expires_at' => Carbon::now()->addMinutes(15),
        ]);

        try {
            Mail::send('emails.verify-code', ['user' => $user, 'code' => $code], function ($message) use ($user) {
                $message->to($user->email)->subject('Verify your email');
            });
        } catch (\Exception $e) {
            \Log::error('Mail sending failed: ' . $e->getMessage());
            return response()->json([
                'message'      => 'Registration succeeded but email failed to send.',
                'user'         => $user,
                'email_failed' => true,
            ], 201);
        }

        return response()->json([
            'message' => 'Registration successful. Please verify your email.',
            'user'    => $user,
        ], 201);
    }

    // -------------------- RESEND EMAIL VERIFICATION --------------------
    public function resendEmailVerification(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();

        if ($user->verified) {
            return response()->json(['message' => 'Email already verified.'], 400);
        }

        $code = Str::upper(Str::random(6));

        $verification = $user->verifications()
            ->where('type', 'email')
            ->whereNull('verified_at')
            ->first();

        if ($verification) {
            $verification->update([
                'code'       => $code,
                'expires_at' => now()->addMinutes(15),
            ]);
        } else {
            Verification::create([
                'user_id'    => $user->id,
                'type'       => 'email',
                'code'       => $code,
                'expires_at' => now()->addMinutes(15),
            ]);
        }

        try {
            Mail::send('emails.verify-code', ['user' => $user, 'code' => $code], function ($message) use ($user) {
                $message->to($user->email)->subject('Resend: Verify your email');
            });
        } catch (\Exception $e) {
            \Log::error('Resend email verification failed: ' . $e->getMessage());
            return response()->json(['message' => 'Email failed to resend.'], 500);
        }

        return response()->json(['message' => 'Verification code resent to email.']);
    }

    // -------------------- VERIFY EMAIL --------------------
    public function verifyEmail(Request $request)
    {
        $request->validate(['email' => 'required|email', 'code' => 'required|string']);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $verification = $user->verifications()
            ->where('type', 'email')
            ->where('code', $request->code)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $verification) {
            return response()->json(['message' => 'Invalid or expired verification code'], 400);
        }

        $verification->update(['verified_at' => now()]);
        $user->update(['verified' => true]);

        return response()->json(['message' => 'Email verified successfully. You can now log in.']);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        // Email does not exist
        if (! $user) {
            return response()->json([
                'errors' => [
                    'email' => 'Email address not found.',
                ],
            ], 422);
        }

        // Password incorrect
        if (! Hash::check($request->password, $user->password)) {
            return response()->json([
                'errors' => [
                    'password' => 'Incorrect password.',
                ],
            ], 422);
        }

        if (! $user->isActive()) {
            return response()->json(['message' => 'Account is deactivated.'], 403);
        }

        if (! $user->verified) {
            return response()->json(['message' => 'Email not verified. Please verify first.'], 403);
        }

        $token = $user->createToken($user->role . '-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user->load('profile'),
        ]);
    }

    // -------------------- LOGOUT --------------------
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    // -------------------- PASSWORD RESET --------------------
    public function requestPasswordReset(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();
        $code = Str::upper(Str::random(6));

        Verification::create([
            'user_id'    => $user->id,
            'type'       => 'password_reset',
            'code'       => $code,
            'expires_at' => now()->addMinutes(15),
        ]);

        try {
            Mail::to($user->email)->send(new PasswordResetCodeMail($user, $code));
        } catch (\Exception $e) {
            \Log::error('Password reset mail failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['message' => 'Reset code sent to email.']);
    }

    public function resendPasswordResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $user = User::where('email', $request->email)->first();
        $code = Str::upper(Str::random(6));

        $verification = $user->verifications()
            ->where('type', 'password_reset')
            ->whereNull('verified_at')
            ->first();

        if ($verification) {
            $verification->update(['code' => $code, 'expires_at' => now()->addMinutes(15)]);
        } else {
            Verification::create([
                'user_id'    => $user->id,
                'type'       => 'password_reset',
                'code'       => $code,
                'expires_at' => now()->addMinutes(15),
            ]);
        }

        try {
            Mail::send('emails.reset-code', ['user' => $user, 'code' => $code], function ($message) use ($user) {
                $message->to($user->email)->subject('Resend: Password Reset Code');
            });
        } catch (\Exception $e) {
            \Log::error('Password reset resend mail failed', [
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json(['message' => 'Password reset code resent to email.']);
    }

    public function verifyResetCode(Request $request)
    {
        $request->validate(['email' => 'required|email', 'code' => 'required|string']);

        $user = User::where('email', $request->email)->first();

        $verification = $user->verifications()
            ->where('type', 'password_reset')
            ->where('code', $request->code)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $verification) {
            return response()->json(['message' => 'Invalid or expired reset code.'], 400);
        }

        $verification->update(['verified_at' => now()]);
        return response()->json(['message' => 'Reset code verified. Proceed to reset password.']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'        => 'required|email',
            'code'         => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = User::where('email', $request->email)->first();

        $verification = $user->verifications()
            ->where('type', 'password_reset')
            ->where('code', $request->code)
            ->whereNotNull('verified_at')
            ->first();

        if (! $verification) {
            return response()->json(['message' => 'Invalid or unverified reset code.'], 400);
        }

        $user->update(['password' => Hash::make($request->new_password)]);
        return response()->json(['message' => 'Password reset successful.']);
    }

    // -------------------- ACCOUNT DEACTIVATION --------------------
    public function deactivate(Request $request)
    {
        $user = $request->user();
        $user->update(['deactivated_at' => now()]);
        $user->tokens()->delete(); // Force logout
        return response()->json(['message' => 'Account deactivated successfully.']);
    }

    // -------------------- ACCOUNT DELETION --------------------
    public function deleteAccount(Request $request)
    {
        $user = $request->user();

        // Delete related data safely
        if ($user->verifications()->exists()) {
            $user->verifications()->delete();
        }

        if (method_exists($user, 'profile') && $user->profile()->exists()) {
            $user->profile()->delete();
        }

        if (method_exists($user, 'orders') && $user->orders()->exists()) {
            $user->orders()->delete();
        }

        $user->delete();
        return response()->json(['message' => 'Account deleted successfully.']);
    }

    public function reactivateAccount(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (! Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($user->isActive()) {
            return response()->json(['message' => 'Account is already active'], 400);
        }

        $user->update(['deactivated_at' => null]);

        // Optionally create a new token
        $token = $user->createToken($user->role . '-token')->plainTextToken;

        return response()->json([
            'message' => 'Account reactivated successfully',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
            'force_change'     => 'sometimes|boolean',
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'Current password is incorrect'], 400);
        }

        if (! $request->force_change && $user->last_password_change) {
            $daysSinceChange = Carbon::parse($user->last_password_change)->diffInDays(now());

            if ($daysSinceChange < 30) {
                return response()->json([
                    'message'        => 'You can only change your password once every 30 days.',
                    'days_remaining' => 30 - $daysSinceChange,
                ], 429);
            }
        }

        $user->password             = Hash::make($request->new_password);
        $user->last_password_change = now();
        $user->save();

        Mail::to($user->email)->send(new PasswordChangedNotification($user));

        return response()->json(['message' => 'Password changed successfully']);
    }

}
