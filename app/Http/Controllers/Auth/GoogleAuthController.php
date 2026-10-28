<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirectToGoogle(): RedirectResponse
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Obtain the user information from Google and authenticate.
     */
    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            Log::warning('Google OAuth authentication failed: '.$e->getMessage());

            return redirect()->route('login')->with('error', 'Gagal masuk dengan Google. Silakan coba kembali.');
        }

        $email = $googleUser->getEmail();
        $googleId = $googleUser->getId();

        if (! $email || ! $googleId) {
            return redirect()->route('login')->with('error', 'Informasi akun Google tidak lengkap.');
        }

        // 1. Find existing user by google_id
        $user = User::where('google_id', $googleId)->first();

        if ($user) {
            if ($googleUser->getAvatar() && $user->avatar !== $googleUser->getAvatar()) {
                $user->update(['avatar' => $googleUser->getAvatar()]);
            }
        } else {
            // 2. Find existing user by email to link Google account
            $user = User::where('email', $email)->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleId,
                    'avatar' => $user->avatar ?: $googleUser->getAvatar(),
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ]);
            } else {
                // 3. Create a new user with customer role
                $user = User::create([
                    'name' => $googleUser->getName() ?: 'Pengguna Google',
                    'email' => $email,
                    'google_id' => $googleId,
                    'avatar' => $googleUser->getAvatar(),
                    'role' => UserRole::CUSTOMER,
                    'email_verified_at' => now(),
                    'password' => null,
                ]);

                event(new Registered($user));
            }
        }

        Auth::login($user, remember: true);

        $request->session()->regenerate();

        return redirect()->intended($user->defaultRedirectUrl());
    }
}

