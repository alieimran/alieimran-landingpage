<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_views', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable();
            $table->string('location')->nullable();
            $table->timestamp('geo_checked_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('page_views', fn (Blueprint $table) => $table->dropColumn(['ip_address', 'location', 'geo_checked_at']));
    }
};
