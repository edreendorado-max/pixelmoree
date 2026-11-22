<?php

namespace App\Http\Controllers;

use App\Mail\NewAccountWelcomeMail;
use App\Models\User;
use App\Notifications\AccountWelcomeNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('register');
    }

    public function store(Request $request): RedirectResponse
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
        Auth::login($user);
        $request->session()->regenerate();

        $redirect = redirect()->route('user.profile-settings')->with('notification_popup', [
            'title' => 'Welcome to Pixmoree, '.$user->name.'!',
            'message' => 'Your account is ready. You can explore the marketplace and share your work.',
        ]);
        $senderAddress = config('mail.from.address');
        if (! is_string($senderAddress) || filter_var($senderAddress, FILTER_VALIDATE_EMAIL) === false) {
            Log::warning('Optional account welcome email skipped because MAIL_FROM_ADDRESS is not configured.', [
                'user_id' => $user->id,
            ]);
        } else {
            try {
                Mail::to($user->email)->send(new NewAccountWelcomeMail($user));
            } catch (TransportExceptionInterface $exception) {
                Log::warning('Optional account welcome email could not be delivered.', [
                    'user_id' => $user->id,
                    'exception' => $exception::class,
                ]);
            }
        }

        return $redirect;
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

        Auth::login($user);
        $request->session()->regenerate();

        $fallbackRoute = $user->isAdmin() ? 'week12.admin' : 'user';
        $intendedUrl = $request->session()->get('url.intended');
        if (! $user->isAdmin() && $this->intendedRouteRequiresAdmin($request, $intendedUrl)) {
            $request->session()->forget('url.intended');

            return redirect()->route($fallbackRoute);
        }

        return redirect()->intended(route($fallbackRoute));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('notification_popup', [
            'title' => 'You are logged out',
            'message' => 'You have been securely signed out of Pixmoree.',
        ]);
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

    private function intendedRouteRequiresAdmin(Request $request, mixed $intendedUrl): bool
    {
        if (! is_string($intendedUrl) || $intendedUrl === '') {
            return false;
        }

        $path = parse_url($intendedUrl, PHP_URL_PATH);
        if (! is_string($path) || $path === '') {
            return false;
        }

        $baseUrl = rtrim($request->getBaseUrl(), '/');
        if ($baseUrl !== '' && ($path === $baseUrl || str_starts_with($path, $baseUrl.'/'))) {
            $path = substr($path, strlen($baseUrl)) ?: '/';
        }

        try {
            $intendedRoute = Route::getRoutes()->match(Request::create('/'.ltrim($path, '/'), 'GET'));
        } catch (NotFoundHttpException|MethodNotAllowedHttpException) {
            return false;
        }

        return in_array('can:manage-admin', $intendedRoute->middleware(), true);
    }
}
