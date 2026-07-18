<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        Gallery::query()->updateOrCreate(['slug' => 'vie-de-ledsp'], [
            'title' => 'La vie de l’EDSP',
            'description' => 'Galerie prête à accueillir les photographies officielles validées par l’établissement.',
            'status' => 'published', 'is_visible' => true, 'position' => 1, 'published_at' => now(),
        ]);
    }
}
