<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    public function run(): void
    {
        $home = $this->page('accueil', 'Accueil', 'home', 'École de Droit et Science Politique — Université de Mahajanga');

        $sections = [
            ['hero', 'hero', 'Comprendre le droit. Agir sur la société.', null, 'L’EDSP vous forme à l’analyse juridique, aux institutions et aux politiques publiques, de la Licence au Master, au cœur de Mahajanga.', 'Découvrir les parcours', '/formations'],
            ['presentation', 'presentation', 'Bienvenue à l’EDSP', 'L’établissement', 'L’EDSP forme des étudiants capables de comprendre, d’analyser et d’accompagner les transformations juridiques, administratives, sociales et politiques de Madagascar.', 'En savoir plus', '/presentation'],
            ['programs', 'programs', 'Nos parcours de formation', 'Formations', 'Deux parcours complémentaires pour comprendre le droit et l’action publique.', 'Voir toutes les formations', '/formations'],
            ['stats', 'stats', null, null, null, null, null],
            ['admissions', 'admissions', 'Admissions et préinscriptions', 'Rejoindre l’EDSP', 'Consultez les conditions, préparez vos pièces et déposez votre dossier pendant une campagne ouverte.', 'Commencer la préinscription', '/preinscription'],
            ['news', 'news', 'Actualités et communiqués', 'À la une', 'Retrouvez les informations académiques et les événements de l’établissement.', 'Toutes les actualités', '/actualites'],
            ['student_life', 'student-life', 'Une expérience universitaire enrichissante', 'Vie étudiante', 'La vie de campus associe apprentissages, activités académiques, conférences et initiatives étudiantes.', 'Découvrir la vie étudiante', '/vie-etudiante'],
            ['library', 'library', 'Bibliothèque et ressources documentaires', 'Ressources', 'Des ressources juridiques, politiques et académiques pour soutenir la formation et la recherche.', 'Découvrir la bibliothèque', '/bibliotheque'],
            ['team', 'team', 'Direction et équipe pédagogique', 'L’équipe', 'Une équipe engagée au service des étudiants et de la qualité académique.', 'Voir l’équipe', '/equipe'],
            ['testimonials', 'testimonials', 'Paroles d’étudiants', 'Ils en parlent', 'Des retours d’expérience présentés à titre de démonstration et administrables depuis le CMS.', null, null],
            ['partners', 'partners', 'Nos partenaires', 'Coopérations', 'Les partenaires institutionnels et académiques peuvent être présentés dans cet espace.', 'Voir les partenaires', '/partenaires'],
            ['cta', 'call-to-action', 'Prêt à rejoindre l’EDSP ?', null, 'Découvrez les formations et préparez votre inscription.', 'Voir les formations', '/formations'],
        ];

        foreach ($sections as $position => [$key, $type, $title, $subtitle, $content, $button, $url]) {
            $home->sections()->updateOrCreate(['section_key' => $key], [
                'section_type' => $type,
                'title' => $title,
                'subtitle' => $subtitle,
                'content' => $content,
                'button_text' => $button,
                'button_url' => $url,
                'settings' => ['background' => in_array($key, ['programs', 'news', 'team'], true) ? 'light' : (in_array($key, ['stats', 'cta'], true) ? 'blue' : 'white'), 'alignment' => in_array($key, ['programs', 'stats', 'admissions', 'team', 'testimonials', 'partners', 'cta'], true) ? 'center' : 'left', 'container' => 'wide'],
                'position' => $position + 1,
                'is_visible' => true,
            ]);
        }

        $pages = [
            ['presentation', 'Présentation de l’EDSP', 'L’EDSP accompagne la formation de juristes et de spécialistes de la science politique au sein de l’Université de Mahajanga.'],
            ['historique', 'Historique', 'Les repères historiques officiels de l’établissement peuvent être renseignés et mis à jour depuis le CMS.'],
            ['missions-et-valeurs', 'Missions et valeurs', 'Exigence académique, esprit critique, service de l’intérêt général et ouverture sur la société structurent le projet de l’EDSP.'],
            ['equipe', 'Direction et équipe', 'Découvrez la direction, les responsables et l’équipe pédagogique de l’EDSP.'],
            ['admissions', 'Admissions', 'Retrouvez les étapes, conditions et calendriers des campagnes d’admission.'],
            ['bibliotheque', 'Bibliothèque', 'La bibliothèque met à disposition des ressources documentaires utiles aux études et à la recherche.'],
            ['vie-etudiante', 'Vie étudiante', 'Activités académiques, conférences et initiatives étudiantes enrichissent l’expérience universitaire.'],
            ['galerie', 'Galerie', 'Découvrez en images la vie académique et les événements de l’établissement.'],
            ['partenaires', 'Partenaires', 'Cet espace présente les coopérations institutionnelles et académiques validées par l’établissement.'],
            ['contact', 'Contact', 'Utilisez le formulaire pour transmettre votre demande à l’EDSP.'],
            ['mentions-legales', 'Mentions légales', 'Les informations légales officielles doivent être complétées par l’établissement avant la mise en production.'],
            ['politique-de-confidentialite', 'Politique de confidentialité', 'Les données transmises sont utilisées uniquement pour traiter les demandes et candidatures, conformément aux règles applicables.'],
        ];

        foreach ($pages as [$slug, $title, $content]) {
            $page = $this->page($slug, $title);
            $page->sections()->updateOrCreate(['section_key' => 'main'], [
                'section_type' => 'rich-content', 'title' => $title, 'content' => $content,
                'settings' => ['background' => 'white', 'alignment' => 'left', 'container' => 'narrow'],
                'position' => 1, 'is_visible' => true,
            ]);
        }
    }

    private function page(string $slug, string $title, string $template = 'default', ?string $description = null): Page
    {
        return Page::query()->updateOrCreate(['slug' => $slug], [
            'title' => $title,
            'status' => 'published',
            'template' => $template,
            'meta_title' => $title.' — EDSP',
            'meta_description' => $description,
            'robots_index' => true,
            'robots_follow' => true,
            'published_at' => now(),
        ]);
    }
}
