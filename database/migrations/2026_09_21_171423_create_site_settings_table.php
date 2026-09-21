<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('display_name');
            $table->string('job_title')->nullable();
            $table->string('tagline')->nullable();
            $table->text('biography')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('location')->nullable();
            $table->string('primary_cta_label')->nullable();
            $table->string('primary_cta_url')->nullable();
            $table->string('secondary_cta_label')->nullable();
            $table->string('secondary_cta_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('contact_notification_email')->nullable();
            $table->string('ga_tracking_id')->nullable();
            $table->boolean('profile_visible')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
