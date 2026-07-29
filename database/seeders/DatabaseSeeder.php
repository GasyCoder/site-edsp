<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            AcademicStructureSeeder::class,
            SettingsSeeder::class,
            PagesSeeder::class,
            AcademicContentSeeder::class,
            NewsSeeder::class,
            CommunitySeeder::class,
            GallerySeeder::class,
            AdmissionsSeeder::class,
            FaqSeeder::class,
        ]);
    }
}
