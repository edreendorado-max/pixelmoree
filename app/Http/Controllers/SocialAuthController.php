<?php

namespace App\Http\Controllers;

use App\Mail\NewAccountWelcomeMail;
use App\Models\User;
use App\Support\EmailDeliveryNotConfigured;
use App\Support\EmailVerification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class SocialAuthController extends Controller
{
    private const PROVIDERS = ['google', 'facebook'];

    public function redirect(string $provider): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        if (! config("services.{$provider}.client_id") || ! config("services.{$provider}.client_secret")) {
            return redirect()->route('login')->withErrors([
                'social' => ucfirst($provider) . ' sign-in is not configured yet.',
            ]);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider, EmailVerification $emailVerification): RedirectResponse
    {
        abort_unless(in_array($provider, self::PROVIDERS, true), 404);

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'social' => ucfirst($provider) . ' sign-in could not be completed. Please try again.',
            ]);
        }

        $providerId = $socialUser->getId();
        if (! is_string($providerId) || $providerId === '') {
            return redirect()->route('login')->withErrors([
                'social' => ucfirst($provider) . ' did not return an account identifier.',
            ]);
        }

        $providerColumn = $provider . '_id';
        $user = User::query()->where($providerColumn, $providerId)->first();
        $isNewAccount = false;

        if (! $user) {
            $email = $socialUser->getEmail();
            if (! is_string($email) || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return redirect()->route('login')->withErrors([
                    'social' => ucfirst($provider) . ' did not provide a usable email address.',
                ]);
            }

            if (User::query()->whereRaw('LOWER(email) = LOWER(?)', [$email])->exists()) {
                return redirect()->route('login')->withErrors([
                    'social' => 'An account already uses this email. Sign in with your password before linking a provider.',
                ]);
            }

            $name = $socialUser->getName() ?: $socialUser->getNickname() ?: 'Pixmoree member';
            $user = User::create([
                'name' => Str::limit(trim($name), 120, ''),
                'email' => $email,
                'password' => Str::random(64),
                $providerColumn => $providerId,
            ]);
            $isNewAccount = true;
        }

        if (! $user->hasVerifiedEmail()) {
            $request->session()->put('email_verification_user_id', $user->id);
            try {
                $sent = $emailVerification->sendCode($user);
            } catch (EmailDeliveryNotConfigured) {
                return redirect()->route('verification.notice')->withErrors([
                    'code' => 'Your account needs email verification, but email delivery is not configured. Configure MAIL_* in .env, then request a new code.',
                ]);
            }

            $redirect = redirect()->route('verification.notice')->with('status', $sent
                ? 'A verification code was sent to your email address.'
                : 'A code was sent recently. Check your inbox before requesting another.');

            if ($isNewAccount) {
                $redirect->with('notification_popup', [
                    'title' => 'Welcome to Pixmoree, '.$user->name.'!',
                    'message' => 'Your account is ready. Check your inbox for a welcome note and verify your email to finish setting up.',
                ]);
                try {
                    Mail::to($user->email)->send(new NewAccountWelcomeMail($user));
                } catch (TransportExceptionInterface) {
                    $redirect->withErrors([
                        'email' => 'Your account was created and the verification code was sent, but we could not send the welcome email.',
                    ]);
                }
            }

            return $redirect;
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route($user->isAdmin() ? 'week12.admin' : 'user'));
    }
}