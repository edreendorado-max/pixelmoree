<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\EmailVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'string', 'min:6', 'max:128', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
            'terms' => ['accepted'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => mb_strtolower(trim($validated['email'])),
            'password' => $validated['password'],
        ]);

        $emailVerification->sendCode($user, false);
        $request->session()->put('email_verification_user_id', $user->id);

        return redirect()->route('verification.notice')
            ->with('status', 'A verification code was sent to your email address. Verify it before signing in.');
    }

    public function login(Request $request, EmailVerification $emailVerification): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();
        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return back()
                ->withErrors(['email' => 'These credentials do not match our records.'])
                ->onlyInput('email');
        }

        if (! $user->hasVerifiedEmail()) {
            $request->session()->put('email_verification_user_id', $user->id);
            $sent = $emailVerification->sendCode($user);

            return redirect()->route('verification.notice')->with('status', $sent
                ? 'A verification code was sent to your email address.'
                : 'A code was sent recently. Check your inbox before requesting another.');
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

        $sent = $emailVerification->sendCode($user);

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
}
