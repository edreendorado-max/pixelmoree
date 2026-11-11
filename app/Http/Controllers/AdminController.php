<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
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

    public function editUser(User $user): View
    {
        return view('week12.admin.user-edit', ['account' => $user]);
    }

    public function updateUser(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'username' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9._-]+$/'],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);
        $validated['email'] = mb_strtolower(trim($validated['email']));
        if (User::query()
            ->whereRaw('LOWER(email) = ?', [$validated['email']])
            ->whereKeyNot($user->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'email' => 'This email address is already assigned to another account.',
            ]);
        }

        $username = trim($validated['username'] ?? '');
        if ($username !== '' && User::query()
            ->whereRaw('LOWER(username) = ?', [mb_strtolower($username)])
            ->whereKeyNot($user->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'username' => 'This username is already assigned to another account.',
            ]);
        }

        $emailChanged = mb_strtolower($user->email) !== mb_strtolower($validated['email']);
        DB::transaction(static function () use ($user, $validated, $username, $emailChanged): void {
            $user->fill([
                'name' => $validated['name'],
                'username' => $username !== '' ? $username : null,
                'email' => mb_strtolower(trim($validated['email'])),
                'phone' => $validated['phone'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]);

            if ($emailChanged) {
                $user->email_verified_at = null;
                DB::table('email_verification_codes')->where('user_id', $user->id)->delete();
            }

            $user->save();
        });

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('status', $emailChanged
                ? 'Account updated. The changed email address must be verified before purchases.'
                : 'Account updated.');
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

    public function analytics(): View
    {
        return view('week12.admin.analytics');
    }

    public function analyticsData(): JsonResponse
    {
        $artworks = \App\Models\Artwork::query()
            ->where('status', 'Listed')
            ->with(['user' => static fn ($query) => $query->withCount(['followers', 'profileViews', 'profileLikes'])])
            ->withCount(['likes', 'views'])
            ->withCount(['pixelCoinTransactions as recent_market_sales_count' => static fn ($query) => $query
                ->where('type', 'purchase')
                ->where('created_at', '>=', now()->subDays(30))])
            ->withMax(['pixelCoinTransactions as last_market_sale_at' => static fn ($query) => $query
                ->where('type', 'purchase')], 'created_at')
            ->latest()
            ->get();

        $creatorSales = DB::table('pixel_coin_transactions as purchases')
            ->join('artworks as sold_artworks', 'sold_artworks.id', '=', 'purchases.artwork_id')
            ->where('purchases.type', 'purchase')
            ->whereNotNull('sold_artworks.user_id')
            ->groupBy('sold_artworks.user_id')
            ->selectRaw('sold_artworks.user_id, COUNT(*) AS sold_count')
            ->pluck('sold_count', 'user_id');
        foreach ($artworks as $artwork) {
            $artwork->setAttribute('creator_sold_count', (int) ($creatorSales[$artwork->user_id] ?? 0));
        }

        $featuredArtworks = $artworks
            ->sortByDesc(static fn ($artwork): int => (int) $artwork->likes_count + (int) $artwork->views_count)
            ->take(8)
            ->values();
        $artworkIds = $featuredArtworks->pluck('id');
        $snapshots = DB::table('artwork_price_snapshots')
            ->whereIn('artwork_id', $artworkIds)
            ->where('snapshot_date', '>=', today()->subDays(30))
            ->orderBy('snapshot_date')
            ->get(['artwork_id', 'snapshot_date', 'price'])
            ->groupBy('artwork_id');

        $creators = User::query()
            ->where('role', '<>', 'admin')
            ->withCount(['followers', 'profileLikes', 'profileViews'])
            ->withCount(['artworks as listed_artworks_count' => static fn ($query) => $query
                ->where('status', 'Listed')])
            ->orderByDesc('followers_count')
            ->limit(8)
            ->get();

        return response()->json([
            'generated_at' => now()->toIso8601String(),
            'price_metric' => 'estimated_market_value',
            'price_note' => 'Daily estimated market-value snapshots; these are not completed sale prices.',
            'artworks' => $featuredArtworks->map(static function ($artwork) use ($snapshots): array {
                $creator = $artwork->user;

                return [
                    'id' => $artwork->id,
                    'title' => $artwork->title,
                    'creator' => $creator?->name ?? $artwork->creator,
                    'asking_price' => (float) $artwork->price,
                    'estimated_market_value' => (float) $artwork->marketEstimate()['value'],
                    'likes' => (int) $artwork->likes_count,
                    'views' => (int) $artwork->views_count,
                    'creator_followers' => (int) ($creator?->followers_count ?? 0),
                    'price_history' => ($snapshots[$artwork->id] ?? collect())->map(static fn ($snapshot): array => [
                        'date' => $snapshot->snapshot_date,
                        'value' => (float) $snapshot->price,
                    ])->values(),
                ];
            })->values(),
            'creators' => $creators->map(static fn (User $creator): array => [
                'name' => $creator->name,
                'followers' => (int) $creator->followers_count,
                'likes' => (int) $creator->profile_likes_count,
                'views' => (int) $creator->profile_views_count,
                'listed_artworks' => (int) $creator->listed_artworks_count,
            ])->values(),
        ]);
    }
}
