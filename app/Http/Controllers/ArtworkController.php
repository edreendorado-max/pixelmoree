<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ArtworkController extends Controller
{
    public function index(Request $request): View
    {
        return view('user.artworks.index', [
            'artworks' => $request->user()->artworks()->latest()->paginate(12),
        ]);
    }

    public function edit(Request $request, string $id): View
    {
        return view('user.artworks.edit', [
            'artwork' => $request->user()->artworks()->findOrFail($id),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:500'],
            'category' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999', 'decimal:0,2'],
            'item_count' => ['sometimes', 'integer', 'min:1', 'max:100000'],
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'originality' => ['accepted'],
        ]);

        $this->createArtwork($validated, $request->user());

        return redirect()
            ->route('user.create')
            ->with('status', 'Your original artwork has been listed.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $artwork = $request->user()->artworks()->findOrFail($id);
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['required', 'string', 'max:500'],
            'category' => ['required', 'string', 'max:50'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999', 'decimal:0,2'],
            'item_count' => ['sometimes', 'integer', 'min:1', 'max:100000'],
            'image' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if (isset($validated['item_count']) && (int) $validated['item_count'] < (int) $artwork->item_count) {
            throw ValidationException::withMessages(['item_count' => 'Edition supply cannot be reduced after item IDs are issued. Increase the supply instead.']);
        }

        $newPath = null;
        $oldPath = null;
        if (isset($validated['image'])) {
            $file = $validated['image'];
            $imageHash = hash_file('sha256', $file->getRealPath());
            if ($imageHash === false) {
                throw new \RuntimeException('The uploaded artwork could not be verified.');
            }
            $this->rejectDuplicateImage($imageHash, $artwork->id);
            $newPath = $file->storePublicly('artworks', 'public');
            if (! is_string($newPath)) {
                throw new \RuntimeException('The replacement artwork image could not be stored.');
            }
            $oldPath = str_starts_with($artwork->image, 'storage/')
                ? substr($artwork->image, strlen('storage/'))
                : null;
            $artwork->image = 'storage/' . $newPath;
            $artwork->image_hash = $imageHash;
        }

        $artwork->fill([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'price' => $validated['price'],
            'item_count' => $validated['item_count'] ?? $artwork->item_count,
        ]);

        try {
            $artwork->save();
            $currentItems = (int) DB::table('artwork_items')->where('artwork_id', $artwork->id)->max('edition_number');
            $rows = [];
            for ($edition = $currentItems + 1; $edition <= (int) $artwork->item_count; $edition++) {
                $rows[] = ['item_id' => (string) Str::uuid(), 'artwork_id' => $artwork->id, 'edition_number' => $edition, 'owner_id' => null, 'status' => 'available', 'created_at' => now(), 'updated_at' => now()];
            }
            foreach (array_chunk($rows, 500) as $chunk) DB::table('artwork_items')->insert($chunk);
        } catch (Throwable $exception) {
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }
            throw $exception;
        }

        if ($oldPath !== null && ! Storage::disk('public')->delete($oldPath)) {
            throw new \RuntimeException('The replaced artwork image could not be removed.');
        }

        return redirect()
            ->route('user.nft-management')
            ->with('status', 'Your artwork listing has been updated.');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $artwork = $request->user()->artworks()->findOrFail($id);
        $storedPath = str_starts_with($artwork->image, 'storage/')
            ? substr($artwork->image, strlen('storage/'))
            : null;
        $artwork->delete();

        if ($storedPath !== null && Storage::disk('public')->exists($storedPath)
            && ! Storage::disk('public')->delete($storedPath)) {
            throw new \RuntimeException('The deleted artwork image could not be removed.');
        }

        return redirect()
            ->route('user.nft-management')
            ->with('status', 'Your artwork listing has been deleted.');
    }

    private function rejectDuplicateImage(string $imageHash, ?string $exceptId = null): void
    {
        $duplicate = Artwork::query()
            ->where('image_hash', $imageHash)
            ->when($exceptId !== null, static fn ($query) => $query->where('id', '!=', $exceptId))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'image' => 'This exact artwork image is already listed. Each artwork can only be listed once.',
            ]);
        }
    }

    private function createArtwork(array $validated, \App\Models\User $owner): Artwork
    {
        $file = $validated['image'] ?? $validated['image_upload'];
        $imageHash = hash_file('sha256', $file->getRealPath());
        if ($imageHash === false) {
            throw new \RuntimeException('The uploaded artwork could not be verified.');
        }
        $this->rejectDuplicateImage($imageHash);

        $storedPath = $file->storePublicly('artworks', 'public');
        if (! is_string($storedPath)) {
            throw new \RuntimeException('The artwork image could not be stored.');
        }

        try {
            return Artwork::create([
                'id' => Str::slug($validated['title']) . '-' . bin2hex(random_bytes(4)),
                'user_id' => $owner->id,
                'title' => $validated['title'],
                'creator' => $owner->name,
                'category' => $validated['category'],
                'price' => $validated['price'],
                'item_count' => $validated['item_count'] ?? 1,
                'image' => 'storage/' . $storedPath,
                'image_hash' => $imageHash,
                'description' => $validated['description'],
                'status' => 'Listed',
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedPath);
            throw $exception;
        }
    }
}
