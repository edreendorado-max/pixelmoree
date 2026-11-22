<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResaleListingController extends Controller
{
    public function store(Request $request, Artwork $artwork): RedirectResponse
    {
        abort_unless(config('payments.enabled'), 503, 'Resales are unavailable during beta.');

        $validated = $request->validate([
            'price_php' => ['required', 'integer', 'min:1', 'max:10000000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000'],
            'evidence' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'ownership_declaration' => ['required', 'accepted'],
        ]);
        $ownedItemCount = DB::table('artwork_items')
            ->where('artwork_id', $artwork->id)
            ->where('owner_id', $request->user()->id)
            ->where('status', 'owned')
            ->whereNotNull('acquired_purchase_id')
            ->count();
        if ($ownedItemCount < (int) $validated['quantity']) {
            throw ValidationException::withMessages([
                'quantity' => 'You can list up to '.$ownedItemCount.' item(s), based on your purchased editions.',
            ]);
        }
        abort_if(DB::table('resale_listings')->where('artwork_id', $artwork->id)
            ->whereIn('status', ['pending_review', 'listed'])->exists(), 409, 'This artwork already has a pending review or active resale listing.');

        $path = $validated['evidence']->store('resale-evidence', 'local');
        if (! is_string($path)) throw new \RuntimeException('Ownership evidence could not be stored securely.');

        DB::transaction(function () use ($request, $artwork, $validated, $path): void {
            Artwork::query()->whereKey($artwork->id)->lockForUpdate()->firstOrFail();
            abort_if(DB::table('resale_listings')->where('artwork_id', $artwork->id)
                ->whereIn('status', ['pending_review', 'listed'])->exists(), 409, 'This artwork already has a pending review or active resale listing.');

            $ownedItems = DB::table('artwork_items')
                ->where('artwork_id', $artwork->id)
                ->where('owner_id', $request->user()->id)
                ->where('status', 'owned')
                ->whereNotNull('acquired_purchase_id')
                ->lockForUpdate()
                ->get(['item_id']);
            if ($ownedItems->count() < (int) $validated['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => 'You can list up to '.$ownedItems->count().' item(s), based on your purchased editions.',
                ]);
            }

            DB::table('resale_listings')->insert([
                'artwork_id' => $artwork->id, 'seller_id' => $request->user()->id,
                'price_minor' => $validated['price_php'] * 100, 'quantity' => $validated['quantity'],
                'evidence_path' => $path,
                'ownership_declaration' => 'Seller confirms they own or are authorized to resell the original work.',
                'status' => 'pending_review', 'created_at' => now(), 'updated_at' => now(),
            ]);
        });

        return back()->with('status', 'Resale submitted for copyright and ownership review. It is not listed until approved.');
    }

    public function review(Request $request, int $listing): RedirectResponse
    {
        abort_unless(config('payments.enabled'), 503, 'Resales are unavailable during beta.');

        $validated = $request->validate(['decision' => ['required', 'in:approve,reject']]);
        $updated = DB::transaction(function () use ($request, $listing, $validated): int {
            $resale = DB::table('resale_listings')
                ->where('id', $listing)
                ->where('status', 'pending_review')
                ->lockForUpdate()
                ->first();
            abort_unless($resale !== null, 409, 'This resale submission was already reviewed or is no longer available.');

            if ($validated['decision'] === 'approve') {
                $ownedItemCount = DB::table('artwork_items')
                    ->where('artwork_id', $resale->artwork_id)
                    ->where('owner_id', $resale->seller_id)
                    ->where('status', 'owned')
                    ->whereNotNull('acquired_purchase_id')
                    ->lockForUpdate()
                    ->get(['item_id'])
                    ->count();
                if ($ownedItemCount < (int) $resale->quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => 'The seller no longer owns enough purchased items for this resale.',
                    ]);
                }
            }

            return DB::table('resale_listings')->where('id', $listing)->where('status', 'pending_review')->update([
                'status' => $validated['decision'] === 'approve' ? 'listed' : 'rejected',
                'reviewed_by' => $request->user()->id,
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);
        });
        abort_unless($updated === 1, 409, 'This resale submission was already reviewed or is no longer available.');

        return back()->with('status', 'Resale review saved. Approval is not a legal determination of copyright.');
    }
}
