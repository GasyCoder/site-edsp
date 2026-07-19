<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @var list<string> */
    private array $tables = [
        'pages', 'page_sections', 'programs', 'news', 'news_categories', 'departments',
        'admission_campaigns', 'team_members', 'testimonials', 'partners', 'galleries',
        'gallery_images', 'documents',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->json('translations')->nullable());
        }

        $this->seedKnownEnglishContent();
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropColumn('translations'));
        }
    }

    private function seedKnownEnglishContent(): void
    {
        $pages = [
            'accueil' => ['title' => 'Home', 'meta_title' => 'School of Law and Political Science — University of Mahajanga'],
            'presentation' => ['title' => 'About EDSP', 'meta_description' => 'EDSP provides education for legal professionals and political science specialists within the University of Mahajanga.'],
            'historique' => ['title' => 'History', 'meta_description' => 'The School’s official historical milestones can be managed and updated through the CMS.'],
            'missions-et-valeurs' => ['title' => 'Mission and values', 'meta_description' => 'Academic excellence, critical thinking, public service and openness to society shape EDSP’s educational vision.'],
            'equipe' => ['title' => 'Leadership and team', 'meta_description' => 'Meet EDSP’s leadership, programme coordinators and teaching staff.'],
            'admissions' => ['title' => 'Admissions', 'meta_description' => 'View the steps, requirements and timetable for current admission rounds.'],
            'bibliotheque' => ['title' => 'Library', 'meta_description' => 'The library provides documentary resources for study and research.'],
            'vie-etudiante' => ['title' => 'Student life', 'meta_description' => 'Academic activities, conferences and student initiatives enrich the university experience.'],
            'galerie' => ['title' => 'Gallery', 'meta_description' => 'Explore academic life and School events through photographs.'],
            'partenaires' => ['title' => 'Partners', 'meta_description' => 'Discover the School’s approved institutional and academic partnerships.'],
            'contact' => ['title' => 'Contact', 'meta_description' => 'Use the contact form to send your enquiry to EDSP.'],
            'mentions-legales' => ['title' => 'Legal notice', 'meta_description' => 'The School’s official legal information is published on this page.'],
            'politique-de-confidentialite' => ['title' => 'Privacy policy', 'meta_description' => 'Submitted data is used solely to process enquiries and applications in accordance with applicable rules.'],
        ];
        foreach ($pages as $slug => $fields) {
            $this->translate('pages', 'slug', $slug, $fields + ['meta_title' => ($fields['title'] ?? '').' — EDSP']);
        }

        $sections = [
            'hero' => ['title' => 'Understand the law. Shape society.', 'content' => 'EDSP equips you to analyse law, institutions and public policy, from Bachelor’s to Master’s level, in the heart of Mahajanga.', 'button_text' => 'Explore our programmes'],
            'presentation' => ['title' => 'Welcome to EDSP', 'subtitle' => 'The School', 'content' => 'EDSP educates students to understand, analyse and support Madagascar’s legal, administrative, social and political transformations.', 'button_text' => 'Learn more'],
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
        foreach ($sections as $key => $fields) {
            $this->translate('page_sections', 'section_key', $key, $fields);
        }

        $this->translate('programs', 'slug', 'droit-prive', [
            'title' => 'Private Law', 'level' => 'Bachelor’s · Master’s', 'domain' => 'Law', 'mention' => 'Private law',
            'description' => 'A programme focused on relationships between individuals, businesses and private institutions.',
            'objectives' => '<ul><li>Build a strong foundation in law.</li><li>Master legal analysis and drafting.</li><li>Understand legal procedures and institutions.</li></ul>',
            'admission_requirements' => 'Requirements are specified for each admission round.',
            'skills' => '<ul><li>Legal reasoning</li><li>Documentary research</li><li>Legal argument and drafting</li></ul>',
            'careers' => 'Legal professions, public administration, business and further study.', 'duration' => 'Bachelor’s and Master’s degrees',
            'curriculum' => 'The official course-unit catalogue is managed through the CMS.', 'meta_title' => 'Private Law programme — EDSP',
            'meta_description' => 'Discover EDSP’s Private Law degree programme.',
        ]);
        $this->translate('programs', 'slug', 'science-politique', [
            'title' => 'Political Science', 'level' => 'Bachelor’s · Master’s', 'domain' => 'Political science', 'mention' => 'Political science',
            'description' => 'A programme focused on institutions, public action, public policy and governance.',
            'objectives' => '<ul><li>Analyse institutions and political systems.</li><li>Understand public action.</li><li>Develop a critical perspective on contemporary transformations.</li></ul>',
            'admission_requirements' => 'Requirements are specified for each admission round.',
            'skills' => '<ul><li>Institutional analysis</li><li>Public policy design and evaluation</li><li>Social science research</li></ul>',
            'careers' => 'Public administration, local authorities, organisations, research and further study.', 'duration' => 'Bachelor’s and Master’s degrees',
            'curriculum' => 'The official course-unit catalogue is managed through the CMS.', 'meta_title' => 'Political Science programme — EDSP',
            'meta_description' => 'Discover EDSP’s Political Science degree programme.',
        ]);

        $news = [
            'ouverture-des-inscriptions' => ['title' => 'Applications are open', 'excerpt' => 'Information about the current application round is available online.', 'content' => '<p>Check the dates, available programmes and required documents before submitting your application.</p>'],
            'calendrier-academique' => ['title' => 'Academic calendar', 'excerpt' => 'Find the key dates in the academic calendar.', 'content' => '<p>Official dates and any updates are published in this section by the School.</p>'],
            'activites-scientifiques-et-conferences' => ['title' => 'Academic events and conferences', 'excerpt' => 'Conferences, meetings and academic activities at EDSP.', 'content' => '<p>Follow announcements about academic events and conferences organised or hosted by EDSP.</p>'],
        ];
        foreach ($news as $slug => $fields) {
            $this->translate('news', 'slug', $slug, $fields + ['meta_title' => $fields['title'].' — EDSP', 'meta_description' => $fields['excerpt']]);
        }

        $this->translate('news_categories', 'slug', 'annonces', ['name' => 'Announcements']);
        $this->translate('news_categories', 'slug', 'vie-academique', ['name' => 'Academic life']);
        $this->translate('news_categories', 'slug', 'evenements', ['name' => 'Events']);
        $this->translate('departments', 'slug', 'droit', ['name' => 'Law', 'description' => 'Teaching and research in law.']);
        $this->translate('departments', 'slug', 'science-politique', ['name' => 'Political Science', 'description' => 'Teaching and research in political science.']);
    }

    /** @param array<string, string> $fields */
    private function translate(string $table, string $column, string $value, array $fields): void
    {
        DB::table($table)->where($column, $value)->update([
            'translations' => json_encode(['en' => array_filter($fields, fn ($item) => filled($item))], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }
};
