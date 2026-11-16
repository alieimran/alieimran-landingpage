<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->string('slug', 80)->nullable()->unique()->after('title');
            $table->boolean('is_public')->default(false)->after('slug');
            // When shared, whether visitors may tick items off (they can never add, edit or delete).
            $table->boolean('allow_ticking')->default(false)->after('is_public');
        });

        // Every checklist gets a link slug; existing rows are backfilled (private until the admin shares them).
        $used = [];
        foreach (DB::table('checklists')->orderBy('id')->get(['id', 'title']) as $row) {
            do {
                $slug = Str::limit(Str::slug($row->title) ?: 'list', 40, '').'-'.Str::lower(Str::random(5));
            } while (isset($used[$slug]));
            $used[$slug] = true;
            DB::table('checklists')->where('id', $row->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('checklists', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'is_public', 'allow_ticking']);
        });
    }
};
