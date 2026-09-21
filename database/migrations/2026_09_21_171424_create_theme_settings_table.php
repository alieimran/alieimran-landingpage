<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_settings', function (Blueprint $table) {
            $table->id();
            $table->string('primary_color')->default('#0a0a0a');
            $table->string('secondary_color')->default('#6b7280');
            $table->string('background_color')->default('#fdfdfc');
            $table->string('text_color')->default('#1b1b18');
            $table->string('button_style')->default('rounded');
            $table->string('border_radius')->default('0.5rem');
            $table->string('font_family')->default('Instrument Sans');
            $table->string('logo_path')->nullable();
            $table->string('favicon_path')->nullable();
            $table->string('background_image')->nullable();
            $table->boolean('dark_mode_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_settings');
    }
};
