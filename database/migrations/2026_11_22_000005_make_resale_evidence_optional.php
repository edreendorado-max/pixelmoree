<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resale_listings', function (Blueprint $table): void {
            $table->string('evidence_path', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('resale_listings')->whereNull('evidence_path')->update(['evidence_path' => '']);

        Schema::table('resale_listings', function (Blueprint $table): void {
            $table->string('evidence_path', 255)->nullable(false)->change();
        });
    }
};
