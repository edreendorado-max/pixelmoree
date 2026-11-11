<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function users(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $search = trim($validated['search'] ?? '');

        return view('week12.admin.users', [
            'users' => User::query()
                ->withCount('artworks')
                ->when($search !== '', static fn ($query) => $query->where(static function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                }))
                ->orderByDesc('created_at')
                ->paginate(25)
                ->withQueryString(),
            'search' => $search,
        ]);
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:user,admin'],
        ]);

        abort_if($request->user()->is($user), 409, 'You cannot change your own administrator role.');

        DB::transaction(static function () use ($user, $validated): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->role === 'admin' && $validated['role'] === 'user') {
                $admins = User::query()->where('role', 'admin')->lockForUpdate()->pluck('id');
                abort_if($admins->count() <= 1, 409, 'The last administrator account cannot be demoted.');
            }

            $lockedUser->forceFill(['role' => $validated['role']])->save();
        });

        return back()->with('status', 'Account role updated.');
    }

    public function updateArtworkPrice(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'price' => ['required', 'numeric', 'min:0', 'max:99999999', 'decimal:0,2'],
        ]);

        $artwork = \App\Models\Artwork::query()->findOrFail($id);
        $artwork->price = $validated['price'];
        $artwork->save();

        return back()->with('status', 'Artwork asking price updated.');
    }
}
