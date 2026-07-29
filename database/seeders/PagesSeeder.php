<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PagesSeeder extends Seeder
{
    public function run(): void
    {
        $directorMessage = '<p>Chères étudiantes, chers étudiants,</p><p>C’est avec un réel plaisir que je vous souhaite la bienvenue à l’École de Droit et Science Politique. Notre établissement place l’exigence académique, l’esprit critique et le sens des responsabilités au cœur de chaque formation.</p><p>Notre ambition est de former des juristes et des spécialistes de la science politique capables de comprendre les transformations de notre société, d’éclairer la décision publique et de contribuer avec intégrité au développement de Madagascar.</p><p>À l’EDSP, vous trouverez une équipe pédagogique engagée, des parcours structurés de la Licence au Master et un environnement favorable à la réussite, à l’ouverture professionnelle et à l’engagement citoyen.</p><p>Je vous invite à construire pleinement votre projet universitaire au sein de notre école.</p>';
        $directorMessageEn = '<p>Dear students,</p><p>It is a genuine pleasure to welcome you to the School of Law and Political Science. Our School places academic excellence, critical thinking and a sense of responsibility at the heart of every programme.</p><p>Our ambition is to educate legal professionals and political science specialists who can understand the transformations affecting our society, inform public decision-making and contribute with integrity to Madagascar’s development.</p><p>At EDSP, you will find a committed teaching team, structured Bachelor’s and Master’s pathways, and an environment that supports achievement, professional development and civic engagement.</p><p>I invite you to build your academic future fully within our School.</p>';

        $home = $this->page(
            'accueil',
            'Accueil',
            'home',
            'Site officiel de l’École de Droit et Sciences Politique de l’Université de Mahajanga.',
        );
        $home->update([
            'meta_title' => 'Accueil | EDSP - Ecole de Droit et Sciences Politique | Université de Mahajanga',
        ]);

        $sections = [
            ['hero', 'hero', 'Comprendre le droit. Agir sur la société.', null, 'L’EDSP vous forme à l’analyse juridique, aux institutions et aux politiques publiques, de la Licence au Master, au cœur de Mahajanga.', 'Découvrir les parcours', '/formations'],
            ['presentation', 'presentation', null, null, null, 'Lire le mot du directeur', '/presentation'],
            ['programs', 'programs', 'Nos parcours de formation', 'Formations', 'Deux parcours complémentaires pour comprendre le droit et l’action publique.', 'Voir toutes les formations', '/formations'],
            ['stats', 'stats', null, null, null, null, null],
            ['admissions', 'admissions', 'Admissions et inscriptions', 'Rejoindre l’EDSP', 'Consultez les conditions, préparez vos pièces et déposez votre dossier pendant une campagne ouverte.', 'Commencer l’inscription', '/inscription'],
            ['news', 'news', 'Actualités et communiqués', 'À la une', 'Retrouvez les informations académiques et les événements de l’établissement.', 'Toutes les actualités', '/actualites'],
            ['student_life', 'student-life', 'Une expérience universitaire enrichissante', 'Vie étudiante', 'La vie de campus associe apprentissages, activités académiques, conférences et initiatives étudiantes.', 'Découvrir la vie étudiante', '/vie-etudiante'],
            ['library', 'library', 'Bibliothèque et ressources documentaires', 'Ressources', 'Des ressources juridiques, politiques et académiques pour soutenir la formation et la recherche.', 'Découvrir la bibliothèque', '/bibliotheque'],
            ['team', 'team', 'Direction et équipe pédagogique', 'L’équipe', 'Une équipe engagée au service des étudiants et de la qualité académique.', 'Voir l’équipe', '/equipe'],
            ['testimonials', 'testimonials', 'Paroles d’étudiants', 'Ils en parlent', 'Des retours d’expérience présentés à titre de démonstration et administrables depuis le CMS.', null, null],
            ['partners', 'partners', 'Nos partenaires', 'Coopérations', 'Les partenaires institutionnels et académiques peuvent être présentés dans cet espace.', 'Voir les partenaires', '/partenaires'],
            ['cta', 'call-to-action', 'Prêt à rejoindre l’EDSP ?', null, 'Découvrez les formations et préparez votre inscription.', 'Voir les formations', '/formations'],
        ];

        foreach ($sections as $position => [$key, $type, $title, $subtitle, $content, $button, $url]) {
            $settings = ['background' => in_array($key, ['programs', 'news', 'team'], true) ? 'light' : (in_array($key, ['stats', 'cta'], true) ? 'blue' : 'white'), 'alignment' => in_array($key, ['programs', 'stats', 'admissions', 'team', 'testimonials', 'partners', 'cta'], true) ? 'center' : 'left', 'container' => 'wide'];
            if ($key === 'hero') {
                $settings = [...$settings,
                    'title_highlight_1' => 'droit', 'title_highlight_1_color' => 'green',
                    'title_highlight_2' => 'science politique', 'title_highlight_2_color' => 'institutional',
                    'title_font_size' => 48,
                    'kicker_text' => 'Deux mentions :', 'rotating_item_1' => 'Droit', 'rotating_item_2' => 'Sciences Politiques',
                ];
            }
            $home->sections()->updateOrCreate(['section_key' => $key], [
                'section_type' => $type,
                'title' => $title,
                'subtitle' => $subtitle,
                'content' => $content,
                'button_text' => $button,
                'button_url' => $url,
                'settings' => $settings,
                'position' => $position + 1,
                'is_visible' => true,
            ]);
        }

        $sectionTranslations = [
            'hero' => ['title' => 'Understand the law. Shape society.', 'content' => 'EDSP equips you to analyse law, institutions and public policy, from Bachelor’s to Master’s level, in the heart of Mahajanga.', 'button_text' => 'Explore our programmes', 'settings' => ['title_highlight_1' => 'law', 'title_highlight_2' => 'political science', 'kicker_text' => 'Two subject areas:', 'rotating_item_1' => 'Law', 'rotating_item_2' => 'Political Science']],
            'presentation' => ['button_text' => "Read the Director's message"],
            'programs' => ['title' => 'Our degree programmes', 'subtitle' => 'Programmes', 'content' => 'Two complementary pathways for understanding law and public affairs.', 'button_text' => 'View all programmes'],
            'admissions' => ['title' => 'Admissions and applications', 'subtitle' => 'Join EDSP', 'content' => 'Review the requirements, prepare your documents and submit your application during an open admission round.', 'button_text' => 'Start your application'],
            'news' => ['title' => 'News and announcements', 'subtitle' => 'Latest news', 'content' => 'Keep up with academic information and events at the School.', 'button_text' => 'All news'],
            'student_life' => ['title' => 'A rewarding university experience', 'subtitle' => 'Student life', 'content' => 'Campus life combines learning, academic activities, conferences and student-led initiatives.', 'button_text' => 'Explore student life'],
            'library' => ['title' => 'Library and learning resources', 'subtitle' => 'Resources', 'content' => 'Legal, political and academic resources supporting teaching and research.', 'button_text' => 'Explore the library'],
            'team' => ['title' => 'Leadership and teaching team', 'subtitle' => 'Our team', 'content' => 'A committed team supporting students and academic excellence.', 'button_text' => 'Meet the team'],
            'testimonials' => ['title' => 'Student voices', 'subtitle' => 'Their experience', 'content' => 'Student experiences managed through the CMS.'],
            'partners' => ['title' => 'Our partners', 'subtitle' => 'Partnerships', 'content' => 'Institutional and academic partners are presented here.', 'button_text' => 'View our partners'],
            'cta' => ['title' => 'Ready to join EDSP?', 'content' => 'Explore our programmes and prepare your application.', 'button_text' => 'View programmes'],
        ];
        foreach ($sectionTranslations as $key => $translation) {
            $home->sections()->where('section_key', $key)->update(['translations' => json_encode(['en' => $translation], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }

        $pages = [
            ['presentation', 'Le mot du directeur', 'Découvrez la vision et le message du directeur de l’École de Droit et Science Politique aux étudiants et futurs candidats.'],
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
            $page = $this->page($slug, $title, 'default', $content);
            $isDirectorMessage = $slug === 'presentation';
            $page->sections()->updateOrCreate(['section_key' => 'main'], [
                'section_type' => $isDirectorMessage ? 'director-message' : 'rich-content',
                'title' => $isDirectorMessage ? 'Pr. Liva Jackson Raharinaivo' : $title,
                'subtitle' => $isDirectorMessage ? 'Mot du directeur' : null,
                'content' => $isDirectorMessage ? $directorMessage : $content,
                'settings' => $isDirectorMessage
                    ? ['background' => 'white', 'alignment' => 'left', 'container' => 'wide', 'director_position' => 'Directeur de l’EDSP', 'director_signature' => 'Avec tous mes encouragements,', 'alt_text' => 'Portrait du directeur de l’EDSP', 'image_zoom' => 100, 'image_position_x' => 50, 'image_position_y' => 50]
                    : ['background' => 'white', 'alignment' => 'left', 'container' => 'narrow'],
                'position' => 1, 'is_visible' => true,
            ]);
        }

        $pageTranslations = [
            'presentation' => ["Director's message", "Discover the Director of the School of Law and Political Science's vision and message to students and prospective applicants."],
            'historique' => ['History', 'The School’s official historical milestones can be managed and updated through the CMS.'],
            'missions-et-valeurs' => ['Mission and values', 'Academic excellence, critical thinking, public service and openness to society shape EDSP’s educational vision.'],
            'equipe' => ['Leadership and team', 'Meet EDSP’s leadership, programme coordinators and teaching staff.'],
            'admissions' => ['Admissions', 'View the steps, requirements and timetable for current admission rounds.'],
            'bibliotheque' => ['Library', 'The library provides documentary resources for study and research.'],
            'vie-etudiante' => ['Student life', 'Academic activities, conferences and student initiatives enrich the university experience.'],
            'galerie' => ['Gallery', 'Explore academic life and School events through photographs.'],
            'partenaires' => ['Partners', 'Discover the School’s approved institutional and academic partnerships.'],
            'contact' => ['Contact', 'Use the contact form to send your enquiry to EDSP.'],
            'mentions-legales' => ['Legal notice', 'The School’s official legal information must be completed before the website goes live.'],
            'politique-de-confidentialite' => ['Privacy policy', 'Submitted data is used solely to process enquiries and applications in accordance with applicable rules.'],
        ];
        foreach ($pageTranslations as $slug => [$title, $content]) {
            $page = Page::query()->where('slug', $slug)->first();
            $page?->update(['translations' => ['en' => ['title' => $title, 'meta_title' => $title.' | EDSP', 'meta_description' => $content]]]);
            $translation = $slug === 'presentation'
                ? ['en' => ['title' => 'Pr. Liva Jackson Raharinaivo', 'subtitle' => "Director's message", 'content' => $directorMessageEn, 'settings' => ['director_position' => 'Director of EDSP', 'director_signature' => 'With my very best wishes,', 'alt_text' => 'Portrait of the Director of EDSP']]]
                : ['en' => ['title' => $title, 'content' => $content]];
            $page?->sections()->where('section_key', 'main')->update(['translations' => json_encode($translation, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)]);
        }
    }

    private function page(string $slug, string $title, string $template = 'default', ?string $description = null): Page
    {
        return Page::query()->updateOrCreate(['slug' => $slug], [
            'title' => $title,
            'status' => 'published',
            'template' => $template,
            'meta_title' => $title.' | EDSP',
            'meta_description' => $description,
            'robots_index' => true,
            'robots_follow' => true,
            'published_at' => now(),
        ]);
    }
}
