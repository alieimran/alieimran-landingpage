<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('prayer_zone', 8)->nullable()->after('maintenance_until');
            // Optional second state whose holidays are shown beside the prayer-zone state, e.g. MY-14.
            $table->string('holiday_extra_state', 8)->nullable()->after('prayer_zone');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', fn (Blueprint $table) => $table->dropColumn(['prayer_zone', 'holiday_extra_state']));
    }
};
