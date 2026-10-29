<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_pages')) {
            return;
        }

        $stored = DB::table('site_pages')->where('slug', 'about')->value('content');
        if (! is_string($stored)) {
            return;
        }

        $content = json_decode($stored, true, flags: JSON_THROW_ON_ERROR);
        $team = $content['team'] ?? [];
        if (! is_array($team)) {
            return;
        }

        if (str_contains(strtolower((string) ($team['eyebrow'] ?? '')), 'creator')) {
            $team['eyebrow'] = '05 / MEET THE DEVELOPERS';
        }
        if (str_contains(strtolower((string) ($team['intro'] ?? '')), 'creator')) {
            $team['intro'] = 'Meet the developers who designed and built the Pixmoree website.';
        }
        $team['privacy_note'] = 'The development team behind the Pixmoree website.';
        $content['team'] = $team;

        DB::table('site_pages')->where('slug', 'about')->update([
            'content' => json_encode($content, JSON_THROW_ON_ERROR),
        ]);
    }

    public function down(): void
    {
        // The corrected team description is content, not a reversible schema change.
    }
};
