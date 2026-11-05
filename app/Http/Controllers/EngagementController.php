<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EngagementController extends Controller
{
    public function showArtwork(Request $request, Artwork $artwork): JsonResponse
    {
        $this->ensureListedArtwork($artwork);

        return response()->json($this->artworkMetrics($request, $artwork));
    }

    public function viewArtwork(Request $request, Artwork $artwork): JsonResponse
    {
        $this->ensureListedArtwork($artwork);

        DB::table('artwork_views')->insertOrIgnore([
            'artwork_id' => $artwork->id,
            'viewer_key' => $this->viewerKey($request),
            'created_at' => now(),
        ]);

        return response()->json($this->artworkMetrics($request, $artwork));
    }

    public function likeArtwork(Request $request, Artwork $artwork): JsonResponse
    {
        $this->ensureListedArtwork($artwork);

        DB::table('artwork_likes')->insertOrIgnore([
            'user_id' => $request->user()->id,
            'artwork_id' => $artwork->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json($this->artworkMetrics($request, $artwork));
    }

    public function unlikeArtwork(Request $request, Artwork $artwork): JsonResponse
    {
        $this->ensureListedArtwork($artwork);
        DB::table('artwork_likes')->where('user_id', $request->user()->id)->where('artwork_id', $artwork->id)->delete();

        return response()->json($this->artworkMetrics($request, $artwork));
    }

    public function showCreator(Request $request, User $creator): JsonResponse
    {
        $this->ensureCreator($creator);

        return response()->json($this->creatorMetrics($request, $creator));
    }

    public function viewCreator(Request $request, User $creator): JsonResponse
    {
        $this->recordCreatorView($request, $creator);

        return response()->json($this->creatorMetrics($request, $creator));
    }

    public function likeCreator(Request $request, User $creator): JsonResponse
    {
        $this->ensureCreator($creator);
        abort_if($request->user()->is($creator), 422, 'You cannot like your own creator profile.');

        DB::table('creator_likes')->insertOrIgnore([
            'user_id' => $request->user()->id,
            'creator_id' => $creator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json($this->creatorMetrics($request, $creator));
    }

    public function unlikeCreator(Request $request, User $creator): JsonResponse
    {
        $this->ensureCreator($creator);
        DB::table('creator_likes')->where('user_id', $request->user()->id)->where('creator_id', $creator->id)->delete();

        return response()->json($this->creatorMetrics($request, $creator));
    }

    public function followCreator(Request $request, User $creator): JsonResponse
    {
        $this->ensureCreator($creator);
        abort_if($request->user()->is($creator), 422, 'You cannot follow your own creator profile.');

        DB::table('creator_follows')->insertOrIgnore([
            'user_id' => $request->user()->id,
            'creator_id' => $creator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json($this->creatorMetrics($request, $creator));
    }

    public function unfollowCreator(Request $request, User $creator): JsonResponse
    {
        $this->ensureCreator($creator);
        DB::table('creator_follows')->where('user_id', $request->user()->id)->where('creator_id', $creator->id)->delete();

        return response()->json($this->creatorMetrics($request, $creator));
    }

    public function recordCreatorView(Request $request, User $creator): void
    {
        $this->ensureCreator($creator);
        if ($request->user()?->is($creator)) return;

        DB::table('creator_views')->insertOrIgnore([
            'creator_id' => $creator->id,
            'viewer_key' => $this->viewerKey($request),
            'created_at' => now(),
        ]);
    }

    public function creatorMetrics(Request $request, User $creator): array
    {
        $this->ensureCreator($creator);

        return [
            'creator_id' => $creator->id,
            'views' => DB::table('creator_views')->where('creator_id', $creator->id)->count(),
            'likes' => DB::table('creator_likes')->where('creator_id', $creator->id)->count(),
            'followers' => DB::table('creator_follows')->where('creator_id', $creator->id)->count(),
            'liked' => $request->user() !== null && DB::table('creator_likes')->where('user_id', $request->user()->id)->where('creator_id', $creator->id)->exists(),
            'following' => $request->user() !== null && DB::table('creator_follows')->where('user_id', $request->user()->id)->where('creator_id', $creator->id)->exists(),
        ];
    }

    private function artworkMetrics(Request $request, Artwork $artwork): array
    {
        return [
            'artwork_id' => $artwork->id,
            'views' => DB::table('artwork_views')->where('artwork_id', $artwork->id)->count(),
            'likes' => DB::table('artwork_likes')->where('artwork_id', $artwork->id)->count(),
            'liked' => $request->user() !== null && DB::table('artwork_likes')->where('user_id', $request->user()->id)->where('artwork_id', $artwork->id)->exists(),
        ];
    }

    private function viewerKey(Request $request): string
    {
        $identity = $request->user() ? 'user:' . $request->user()->id : 'session:' . $request->session()->getId();

        return hash('sha256', $identity);
    }

    private function ensureListedArtwork(Artwork $artwork): void
    {
        abort_unless($artwork->status === 'Listed', 404);
    }

    private function ensureCreator(User $creator): void
    {
        abort_if($creator->isAdmin(), 404);
    }
}