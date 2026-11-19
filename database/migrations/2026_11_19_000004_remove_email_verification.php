<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('email_verification_codes');
        $this->replaceMarketplaceNote(
            'Purchases require email verification and manual admin GCash review. Each edition item receives one recorded owner in Pixmoree; no blockchain transfer is provided.',
            'Purchases require an account and manual admin GCash review. Each edition item receives one recorded owner in Pixmoree; no blockchain transfer is provided.',
        );

        if (Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', static function (Blueprint $table): void {
                $table->dropColumn('email_verified_at');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'email_verified_at')) {
            Schema::table('users', static function (Blueprint $table): void {
                $table->timestamp('email_verified_at')->nullable();
            });
        }

        if (! Schema::hasTable('email_verification_codes')) {
            Schema::create('email_verification_codes', static function (Blueprint $table): void {
                $table->foreignId('user_id')->primary()->constrained()->cascadeOnDelete();
                $table->char('code_hash', 64);
                $table->timestamp('expires_at');
                $table->timestamp('last_sent_at')->useCurrent();
                $table->unsignedTinyInteger('attempts')->default(0);
                $table->timestamps();
            });
        }

        $this->replaceMarketplaceNote(
            'Purchases require an account and manual admin GCash review. Each edition item receives one recorded owner in Pixmoree; no blockchain transfer is provided.',
            'Purchases require email verification and manual admin GCash review. Each edition item receives one recorded owner in Pixmoree; no blockchain transfer is provided.',
        );
    }

    private function replaceMarketplaceNote(string $oldNote, string $newNote): void
    {
        if (! Schema::hasTable('site_pages')) {
            return;
        }

        $page = DB::table('site_pages')->where('slug', 'marketplace')->first();
        $content = $page === null ? null : json_decode((string) $page->content, true);
        if (! is_array($content) || data_get($content, 'hero.note') !== $oldNote) {
            return;
        }

        data_set($content, 'hero.note', $newNote);
        DB::table('site_pages')->where('slug', 'marketplace')->update([
            'content' => json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }
};
