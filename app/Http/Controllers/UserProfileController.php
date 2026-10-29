<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class UserProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('user.profile-settings', [
            'user' => $request->user(),
            'images' => $request->user()->images()->latest()->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:254', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string', 'max:30'],
            'bio' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }
        $user->fill($validated)->save();

        return redirect()
            ->route('user.profile-settings')
            ->with('status', 'Your profile has been updated.');
    }

    public function storeImage(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $validated['image'];
        $path = $file->store('images', 'public');

        try {
            $request->user()->images()->create([
                'path' => $path,
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'size_bytes' => $file->getSize(),
                'alt_text' => $validated['alt_text'] ?? null,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($path);
            throw $exception;
        }

        return redirect()
            ->route('user.profile-settings')
            ->with('status', 'Your image has been uploaded.');
    }

    public function updateImage(Request $request, int $imageId): RedirectResponse
    {
        $validated = $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);

        $image = $request->user()->images()->findOrFail($imageId);
        $image->update(['alt_text' => $validated['alt_text'] ?? null]);

        return redirect()
            ->route('user.profile-settings')
            ->with('status', 'Image details have been updated.');
    }

    public function destroyImage(Request $request, int $imageId): RedirectResponse
    {
        $image = $request->user()->images()->findOrFail($imageId);
        $disk = Storage::disk('public');

        if ($disk->exists($image->path) && ! $disk->delete($image->path)) {
            throw new \RuntimeException('The uploaded image file could not be deleted.');
        }

        $image->delete();

        return redirect()
            ->route('user.profile-settings')
            ->with('status', 'Your image has been deleted.');
    }
}
