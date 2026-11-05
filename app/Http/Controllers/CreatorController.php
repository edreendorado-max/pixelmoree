<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreatorController extends Controller
{
    public function show(Request $request, User $creator, EngagementController $engagement): View
    {
        abort_if($creator->isAdmin(), 404);

        $engagement->recordCreatorView($request, $creator);
        $artworks = $creator->artworks()
            ->where('status', 'Listed')
            ->withCount(['views', 'likes'])
            ->latest()
            ->get();

        return view('creators.show', [
            'creator' => $creator,
            'artworks' => $artworks,
            'engagement' => $engagement->creatorMetrics($request, $creator),
            'likedArtworkIds' => $request->user()?->likedArtworks()->pluck('artworks.id')->all() ?? [],
        ]);
    }
}