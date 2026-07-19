<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\NewsCategory;
use Illuminate\Database\Seeder;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $announcement = NewsCategory::query()->updateOrCreate(['slug' => 'annonces'], ['name' => 'Annonces']);
        $academic = NewsCategory::query()->updateOrCreate(['slug' => 'vie-academique'], ['name' => 'Vie académique']);
        $events = NewsCategory::query()->updateOrCreate(['slug' => 'evenements'], ['name' => 'Événements']);

        $items = [
            ['ouverture-des-inscriptions', 'Ouverture des inscriptions', 'Les informations relatives à la campagne d’inscription sont accessibles en ligne.', 'Consultez les dates, les formations ouvertes et les pièces demandées avant de transmettre votre dossier.', $announcement->id, true],
            ['calendrier-academique', 'Calendrier académique', 'Retrouvez les principales informations du calendrier universitaire.', 'Les dates officielles et leurs éventuelles mises à jour sont publiées dans cet espace par l’établissement.', $academic->id, false],
            ['activites-scientifiques-et-conferences', 'Activités scientifiques et conférences', 'Conférences, rencontres et activités académiques de l’EDSP.', 'Suivez les annonces relatives aux activités scientifiques et aux conférences organisées ou accueillies par l’EDSP.', $events->id, false],
        ];

        foreach ($items as $index => [$slug, $title, $excerpt, $content, $categoryId, $featured]) {
            News::query()->updateOrCreate(['slug' => $slug], [
                'title' => $title, 'excerpt' => $excerpt, 'content' => '<p>'.$content.'</p>',
                'category_id' => $categoryId, 'status' => 'published', 'is_featured' => $featured,
                'meta_title' => $title.' — EDSP', 'meta_description' => $excerpt,
                'robots_index' => true, 'robots_follow' => true,
                'published_at' => now()->subDays($index + 1),
            ]);
        }
    }
}
