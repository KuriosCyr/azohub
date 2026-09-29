<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TwoFactorCode;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

class TwoFactorChallengeController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        if (!$request->session()->has('2fa_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('2fa_user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $request->validate(['code' => 'required|string']);

        // Brute-force d'un code à 6 chiffres (1 million de combinaisons) : sans limite,
        // automatisable en un temps raisonnable. Au-delà de 5 essais, il faut redemander un
        // code (voir resend()) plutôt que continuer à en tenter d'autres sur le même code.
        $limiterKey = 'two-factor-attempt:' . $userId;

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $request->session()->forget(['2fa_user_id', '2fa_code', '2fa_expires_at', '2fa_remember']);

            return redirect()->route('login')
                ->withErrors(['email' => 'Trop de tentatives. Reconnectez-vous pour recevoir un nouveau code.']);
        }

        $expiresAt = $request->session()->get('2fa_expires_at');
        $expectedCode = $request->session()->get('2fa_code');

        if (!$expiresAt || now()->timestamp > $expiresAt) {
            return back()->withErrors(['code' => 'Ce code a expiré. Demandez-en un nouveau.']);
        }

        if (!hash_equals((string) $expectedCode, (string) $request->input('code'))) {
            RateLimiter::hit($limiterKey, 600);

            return back()->withErrors(['code' => 'Code incorrect.']);
        }

        RateLimiter::clear($limiterKey);

        $user = User::findOrFail($userId);
        $remember = (bool) $request->session()->get('2fa_remember');

        $request->session()->forget(['2fa_user_id', '2fa_code', '2fa_expires_at', '2fa_remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    public function resend(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('2fa_user_id');

        if (!$userId) {
            return redirect()->route('login');
        }

        $limiterKey = 'two-factor-resend:' . $userId;

        if (RateLimiter::tooManyAttempts($limiterKey, 3)) {
            $seconds = RateLimiter::availableIn($limiterKey);

            return back()->withErrors(['code' => "Veuillez patienter {$seconds} seconde(s) avant de redemander un code."]);
        }

        RateLimiter::hit($limiterKey, 300);

        $user = User::findOrFail($userId);
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put('2fa_code', $code);
        $request->session()->put('2fa_expires_at', now()->addMinutes(10)->timestamp);

        $user->notify(new TwoFactorCode($code));

        return back()->with('success', 'Un nouveau code vous a été envoyé.');
    }
}
