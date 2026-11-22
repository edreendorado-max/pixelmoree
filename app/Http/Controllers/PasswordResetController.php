<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\MarketplaceNotification;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function createLink(): View
    {
        return view('auth.forgot-password');
    }

    public function sendLink(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:254'],
        ]);
        $email = mb_strtolower(trim($validated['email']));

        if (config('app.env') === 'local') {
            $user = Password::broker()->getUser(['email' => $email]);
            $simulatedResetUrl = $user === null
                ? null
                : route('password.reset', [
                    'token' => Password::broker()->createToken($user),
                    'email' => $user->email,
                ]);

            return back()
                ->with('status', 'If an account exists for that email, a reset link is ready.')
                ->with('simulated_reset_url', $simulatedResetUrl);
        }

        Password::sendResetLink(['email' => $email]);

        return back()->with('status', 'If an account exists for that email, a reset link has been sent.');
    }

    public function createForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'email' => $request->query('email'),
            'token' => $token,
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:254'],
            'password' => ['required', 'confirmed', 'string', 'min:6', 'max:128', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[^A-Za-z0-9]/'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                $user->notify(new MarketplaceNotification('Password reset complete', 'Your Pixmoree password was changed successfully. If you did not make this change, contact support.', route('login')));

                event(new PasswordReset($user));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withErrors(['email' => __($status)]);
        }

        return redirect()->route('login')->with('status', 'Your password has been reset. You can now sign in.');
    }
}
