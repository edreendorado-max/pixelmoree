<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\AccountWelcomeNotification;
use App\Support\EmailDeliveryNotConfigured;
use App\Support\EmailVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('register');
    }

    public function store(Request $request, EmailVerification $emailVerification): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['sometimes', 'nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9._-]+$/', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'string', 'min:6', 'max:128', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
            'terms' => ['accepted'],
        ]);

        $username = trim($validated['username'] ?? '');
        if ($username !== '' && User::query()->whereRaw('LOWER(username) = ?', [mb_strtolower($username)])->exists()) {
            throw ValidationException::withMessages([
                'username' => 'This username is already taken.',
            ]);
        }

        if ($username === '') {
            $username = $this->availableUsername(Str::slug($validated['name'], '') ?: 'creator');
        }

        $user = User::create([
            'name' => $validated['name'],
            'username' => $username,
            'email' => mb_strtolower(trim($validated['email'])),
            'password' => $validated['password'],
        ]);
        $user->notify(new AccountWelcomeNotification());

        $request->session()->put('email_verification_user_id', $user->id);
        try {
            $emailVerification->sendCode($user, false);
        } catch (EmailDeliveryNotConfigured) {
            return redirect()->route('verification.notice')->withErrors([
                'code' => 'Your account was created, but we could not send a verification email. Configure MAIL_* in .env, then request a new code.',
            ]);
        }

        return redirect()->route('verification.notice')
            ->with('status', 'A verification code was sent to your email address. Verify it before signing in.');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'max:254'],
            'password' => ['required', 'string'],
        ]);

        $identity = trim($credentials['email']);
        $normalizedEmail = mb_strtolower($identity);
        $user = User::query()
            ->where(static fn ($query) => $query
                ->whereRaw('LOWER(email) = ?', [$normalizedEmail])
                ->orWhereRaw('LOWER(username) = ?', [$normalizedEmail]))
            ->first();
        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        if (! $user->hasVerifiedEmail()) {
            $request->session()->put('email_verification_user_id', $user->id);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route($user->isAdmin() ? 'week12.admin' : 'user'));
    }

    public function verificationNotice(Request $request): RedirectResponse|View
    {
        $user = User::query()->find($request->session()->get('email_verification_user_id'));
        if ($user === null) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sign in or create an account to verify an email address.',
            ]);
        }
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'Your email is verified. You can now sign in.');
        }

        return view('auth.verify-email', ['user' => $user]);
    }

    public function verifyEmail(Request $request, EmailVerification $emailVerification): RedirectResponse
    {
        $validated = $request->validate(['code' => ['required', 'digits:6']]);
        $user = User::query()->findOrFail($request->session()->get('email_verification_user_id'));
        if (! $emailVerification->verify($user, $validated['code'])) {
            throw ValidationException::withMessages(['code' => 'That verification code is incorrect.']);
        }

        Auth::login($user->fresh());
        $request->session()->forget('email_verification_user_id');
        $request->session()->regenerate();

        return redirect()->route('user.profile-settings')
            ->with('status', 'Your email has been verified and your account is ready.');
    }

    public function resendVerification(Request $request, EmailVerification $emailVerification): RedirectResponse
    {
        $user = User::query()->findOrFail($request->session()->get('email_verification_user_id'));
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', 'Your email is already verified.');
        }

        try {
            $sent = $emailVerification->sendCode($user);
        } catch (EmailDeliveryNotConfigured) {
            return back()->withErrors([
                'code' => 'Email delivery is not configured. Configure MAIL_* in .env, then request a new code.',
            ]);
        }

        return back()->with('status', $sent
            ? 'A new verification code was sent to your email address.'
            : 'A code was sent recently. Check your inbox before requesting another.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function availableUsername(string $base): string
    {
        $username = Str::limit($base, 30, '');
        $suffix = 1;

        while (User::query()->whereRaw('LOWER(username) = ?', [mb_strtolower($username)])->exists()) {
            $ending = (string) $suffix++;
            $username = Str::limit($base, 30 - strlen($ending), '').$ending;
        }

        return $username;
    }
}
