<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('link_category_id')->nullable()->constrained('link_categories')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url');
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->boolean('enabled')->default(true);
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('open_in_new_tab')->default(true);
            $table->timestamps();

            $table->index(['enabled', 'featured', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('links');
    }
};
