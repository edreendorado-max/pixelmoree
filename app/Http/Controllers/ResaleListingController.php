<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResaleListingController extends Controller
{
    public function store(Request $request, Artwork $artwork): RedirectResponse
    {
        $validated = $request->validate([
            'price_php' => ['required', 'integer', 'min:1', 'max:10000000'],
            'evidence' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'ownership_declaration' => ['required', 'accepted'],
        ]);
        abort_unless(DB::table('artwork_ownerships')
            ->where('artwork_id', $artwork->id)
            ->where('owner_id', $request->user()->id)
            ->exists(), 403, 'Only the current verified owner can submit this artwork for resale.');
        abort_if(DB::table('resale_listings')->where('artwork_id', $artwork->id)
            ->whereIn('status', ['pending_review', 'listed'])->exists(), 409, 'This artwork already has a pending review or active resale listing.');
        $path = $validated['evidence']->store('resale-evidence', 'local');
        if (! is_string($path)) throw new \RuntimeException('Ownership evidence could not be stored securely.');

        DB::table('resale_listings')->insert([
            'artwork_id' => $artwork->id, 'seller_id' => $request->user()->id,
            'price_minor' => $validated['price_php'] * 100, 'evidence_path' => $path,
            'ownership_declaration' => 'Seller confirms they own or are authorized to resell the original work.',
            'status' => 'pending_review', 'created_at' => now(), 'updated_at' => now(),
        ]);

        return back()->with('status', 'Resale submitted for copyright and ownership review. It is not listed until approved.');
    }

    public function review(Request $request, int $listing): RedirectResponse
    {
        $validated = $request->validate(['decision' => ['required', 'in:approve,reject']]);
        $updated = DB::table('resale_listings')->where('id', $listing)->where('status', 'pending_review')->update([
            'status' => $validated['decision'] === 'approve' ? 'listed' : 'rejected',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'updated_at' => now(),
        ]);
        abort_unless($updated === 1, 409, 'This resale submission was already reviewed or is no longer available.');

        return back()->with('status', 'Resale review saved. Approval is not a legal determination of copyright.');
    }
}
