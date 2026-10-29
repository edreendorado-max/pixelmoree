<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class UserProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('user.profile-settings', [
            'user' => $request->user(),
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
            'avatar' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
            'banner' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
        ]);
        $avatar = $validated['avatar'] ?? null;
        $banner = $validated['banner'] ?? null;
        unset($validated['avatar'], $validated['banner']);

        if ($user->email !== $validated['email']) {
            $user->email_verified_at = null;
        }

        $previousPaths = [
            'avatar_path' => $user->avatar_path,
            'banner_path' => $user->banner_path,
        ];
        $uploadDirectories = [
            'avatar_path' => 'avatars',
            'banner_path' => 'banners',
        ];
        $newPaths = [];

        try {
            $user->fill($validated);
            foreach ([
                'avatar_path' => $avatar,
                'banner_path' => $banner,
            ] as $attribute => $file) {
                if ($file === null) {
                    continue;
                }

                $path = $file->storePublicly($uploadDirectories[$attribute], 'public');
                if (! is_string($path)) {
                    throw new RuntimeException('The profile image could not be stored.');
                }

                $newPaths[$attribute] = $path;
                $user->setAttribute($attribute, 'storage/' . $path);
            }
            $user->save();
        } catch (Throwable $exception) {
            foreach ($newPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $exception;
        }

        foreach ($newPaths as $attribute => $path) {
            $previousPath = $previousPaths[$attribute];
            $storagePrefix = 'storage/' . $uploadDirectories[$attribute] . '/';

            if (is_string($previousPath) && str_starts_with($previousPath, $storagePrefix)) {
                $oldPath = substr($previousPath, strlen('storage/'));
                if (Storage::disk('public')->exists($oldPath) && ! Storage::disk('public')->delete($oldPath)) {
                    throw new RuntimeException('The previous profile image could not be removed.');
                }
            }
        }

        return redirect()
            ->route('user.profile-settings')
            ->with('status', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'string', 'min:8', 'max:128', 'confirmed', 'different:current_password'],
        ]);

        $request->user()->forceFill([
            'password' => Hash::make($validated['new_password']),
        ])->save();

        $request->session()->regenerate();

        return redirect()
            ->route('user.profile-settings')
            ->with('status', 'Your password has been changed.');
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
