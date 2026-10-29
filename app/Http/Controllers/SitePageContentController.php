<?php

namespace App\Http\Controllers;

use App\Models\SitePage;
use App\Support\SitePageContent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class SitePageContentController extends Controller
{
    public function edit(string $slug): View
    {
        $definition = SitePageContent::definitions()[$slug] ?? abort(404);

        return view('week12.admin.content', [
            'slug' => $slug,
            'definition' => $definition,
            'content' => SitePageContent::get($slug),
            'pageSlugs' => array_keys(SitePageContent::definitions()),
        ]);
    }

    public function update(Request $request, string $slug): RedirectResponse
    {
        $definition = SitePageContent::definitions()[$slug] ?? abort(404);
        $rules = [
            'content' => ['sometimes', 'array'],
            'images' => ['sometimes', 'array'],
        ];

        foreach ($definition['groups'] as $group) {
            foreach ($group['fields'] as $field) {
                $key = $field['key'];

                if ($field['type'] === 'image') {
                    $rules["images.{$key}"] = ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:20480'];
                    continue;
                }

                $fieldRules = ['nullable', 'string', 'max:' . $field['max']];
                if ($field['type'] === 'email') {
                    $fieldRules[] = 'email';
                } elseif ($field['type'] === 'date') {
                    $fieldRules[] = 'date_format:Y-m-d';
                }

                $rules["content.{$key}"] = $fieldRules;
            }
        }

        $validated = $request->validate($rules);
        $content = SitePageContent::get($slug);

        foreach ($definition['groups'] as $group) {
            foreach ($group['fields'] as $field) {
                if ($field['type'] === 'image') {
                    continue;
                }

                $key = $field['key'];
                if (data_get($validated, "content.{$key}") !== null || data_has($request->input('content', []), $key)) {
                    data_set($content, $key, data_get($validated, "content.{$key}") ?? '');
                }
            }
        }

        $newFiles = [];
        $oldFiles = [];
        foreach ($definition['groups'] as $group) {
            foreach ($group['fields'] as $field) {
                if ($field['type'] !== 'image') {
                    continue;
                }

                $file = data_get($request->file('images', []), $field['key']);
                if ($file === null) {
                    continue;
                }

                $previousPath = data_get($content, $field['key']);
                $storedPath = $file->storePublicly('site-pages', 'public');
                $newFiles[] = $storedPath;
                data_set($content, $field['key'], 'storage/' . $storedPath);

                if (is_string($previousPath) && str_starts_with($previousPath, 'storage/site-pages/')) {
                    $oldFiles[] = substr($previousPath, strlen('storage/'));
                }
            }
        }

        $page = SitePage::firstOrNew(['slug' => $slug]);
        $page->content = $content;

        try {
            DB::transaction(static fn () => $page->save());
        } catch (Throwable $exception) {
            if ($newFiles !== []) {
                Storage::disk('public')->delete($newFiles);
            }

            throw $exception;
        }

        if ($oldFiles !== []) {
            Storage::disk('public')->delete($oldFiles);
        }

        return redirect()
            ->route('admin.content.edit', ['slug' => $slug])
            ->with('status', 'Website content saved.');
    }
}
