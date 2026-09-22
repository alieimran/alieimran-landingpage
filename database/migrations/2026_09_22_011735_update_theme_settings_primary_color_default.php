<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The original default (#0a0a0a) predates the emerald cybersecurity
 * theme and never matched any color actually used in the design
 * system — fixing it here rather than editing the original migration,
 * since that one has already run.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->string('primary_color')->default('#10b981')->change();
        });

        DB::table('theme_settings')->where('primary_color', '#0a0a0a')->update(['primary_color' => '#10b981']);
    }

    public function down(): void
    {
        Schema::table('theme_settings', function (Blueprint $table) {
            $table->string('primary_color')->default('#0a0a0a')->change();
        });
    }
};
