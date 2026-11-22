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

        $page = DB::table('site_pages')->where('slug', 'marketplace')->first();
        $content = $page === null ? null : json_decode((string) $page->content, true);
        if (! is_array($content)) {
            return;
        }

        if (data_get($content, 'hero.note') === 'Purchases require an account and manual admin GCash review. Each edition item receives one recorded owner in Pixmoree; no blockchain transfer is provided.') {
            data_set($content, 'hero.note', 'Purchases require an account and run in simulation mode. Each edition item receives one recorded owner in Pixmoree; no real payment or blockchain transfer is provided.');
        }
        if (data_get($content, 'footer.disclaimer') === 'GCash payments go to the configured Pixmoree admin account. Admins manually verify receipts; creators receive Pixel Coins after approval.') {
            data_set($content, 'footer.disclaimer', 'Purchases and creator withdrawals are simulations. Receipts and edition ownership are recorded in Pixmoree; no real payment or payout occurs.');
        }

        DB::table('site_pages')->where('slug', 'marketplace')->update([
            'content' => json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Existing editable page content is not reverted to outdated payment instructions.
    }
};
