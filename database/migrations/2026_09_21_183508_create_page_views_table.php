<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_views', function (Blueprint $table) {
            $table->id();
            $table->string('path');
            $table->string('referrer')->nullable();
            $table->string('device_type', 20)->nullable();
            $table->string('browser', 50)->nullable();
            $table->string('os', 50)->nullable();

            // Daily-rotating hash of IP + user agent, never the raw IP.
            // Lets us estimate unique visitors within a short window
            // without being able to identify or track a visitor across
            // days. See SRS §38 (privacy-conscious analytics).
            $table->string('visitor_hash', 64)->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['path', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('page_views');
    }
};
