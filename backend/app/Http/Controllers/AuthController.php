<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    // S'inscrire
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'phone'    => 'nullable|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Send email verification
        $this->sendVerificationEmail($user);

        return response()->json([
            'message' => 'Compte créé ! Veuillez vérifier votre email avant de vous connecter.',
        ], 201);
    }

    // Send verification email
    private function sendVerificationEmail(User $user): void
    {
        $token = Str::random(64);

        DB::table('email_verification_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => $token, 'created_at' => now()]
        );

        $verificationUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173'))
            . '/verify-email?token=' . $token . '&email=' . urlencode($user->email);

        $user->notify(new VerifyEmailNotification($verificationUrl));
    }

    // Verify email
    public function verifyEmail(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
        ]);

        $record = DB::table('email_verification_tokens')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$record) {
            return response()->json(['message' => 'Lien de vérification invalide ou expiré.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        $user->update(['email_verified_at' => now()]);
        DB::table('email_verification_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Email vérifié avec succès ! Vous pouvez maintenant vous connecter.']);
    }

    // Resend verification email
    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Cet email est déjà vérifié.'], 400);
        }

        $this->sendVerificationEmail($user);

        return response()->json(['message' => 'Email de vérification renvoyé.']);
    }

    // Se connecter
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Email ou mot de passe incorrect.'], 401);
        }

        $user = Auth::user();

        if (!$user->email_verified_at) {
            Auth::logout();
            return response()->json([
                'message'           => 'Veuillez vérifier votre email avant de vous connecter.',
                'email_not_verified' => true,
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Connexion réussie',
            'user'    => $user,
            'token'   => $token,
        ]);
    }

    // Mot de passe oublié - envoyer lien
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        // Always return success to avoid email enumeration
        if (!$user) {
            return response()->json(['message' => 'Si cet email existe, un lien de réinitialisation a été envoyé.']);
        }

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            ['token' => Hash::make($token), 'created_at' => now()]
        );

        $resetUrl = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173'))
            . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);

        $user->notify(new ResetPasswordNotification($resetUrl));

        return response()->json(['message' => 'Si cet email existe, un lien de réinitialisation a été envoyé.']);
    }

    // Réinitialiser le mot de passe
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'token'    => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return response()->json(['message' => 'Token invalide ou expiré.'], 400);
        }

        // Check expiry (60 minutes)
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return response()->json(['message' => 'Le lien de réinitialisation a expiré.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Utilisateur introuvable.'], 404);
        }

        $user->update(['password' => Hash::make($request->password)]);
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Mot de passe réinitialisé avec succès !']);
    }

    // Se déconnecter
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Déconnexion réussie']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
