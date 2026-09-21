<?php

namespace Database\Seeders;

use App\Models\LinkCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class LinkCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Professional',
            'Projects',
            'Technology',
            'Social',
            'Events',
            'Personal',
        ];

        foreach ($categories as $index => $name) {
            LinkCategory::query()->firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'sort_order' => $index]
            );
        }
    }
}
