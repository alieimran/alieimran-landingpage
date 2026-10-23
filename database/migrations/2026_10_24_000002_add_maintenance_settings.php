<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('maintenance_enabled')->default(false);
            $table->text('maintenance_reason')->nullable();
            $table->timestamp('maintenance_started_at')->nullable();
            $table->timestamp('maintenance_until')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', fn (Blueprint $table) => $table->dropColumn(['maintenance_enabled', 'maintenance_reason', 'maintenance_started_at', 'maintenance_until']));
    }
};
