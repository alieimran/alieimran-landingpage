<?php

namespace Database\Seeders;

use App\Models\SiteSection;
use Illuminate\Database\Seeder;

class SiteSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            ['key' => 'hero', 'title' => 'Profile', 'sort_order' => 1],
            ['key' => 'featured_links', 'title' => 'Featured', 'sort_order' => 2],
            ['key' => 'links', 'title' => 'Links', 'sort_order' => 3],
            ['key' => 'social_links', 'title' => 'Social', 'sort_order' => 4],
            ['key' => 'contact', 'title' => 'Contact', 'sort_order' => 5],
        ];

        foreach ($sections as $section) {
            SiteSection::query()->firstOrCreate(
                ['key' => $section['key']],
                $section
            );
        }
    }
}
