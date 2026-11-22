<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resale_listings', function (Blueprint $table): void {
            $table->unsignedInteger('quantity')->default(1);
        });
    }

    public function down(): void
    {
        Schema::table('resale_listings', function (Blueprint $table): void {
            $table->dropColumn('quantity');
        });
    }
};
