<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Program;
use Illuminate\Database\Seeder;

class AcademicContentSeeder extends Seeder
{
    public function run(): void
    {
        $law = Department::query()->updateOrCreate(['slug' => 'droit'], ['name' => 'Droit', 'description' => 'Enseignements et recherche en droit.']);
        $politics = Department::query()->updateOrCreate(['slug' => 'science-politique'], ['name' => 'Science politique', 'description' => 'Enseignements et recherche en science politique.']);
        $law->update(['translations' => ['en' => ['name' => 'Law', 'description' => 'Teaching and research in law.']]]);
        $politics->update(['translations' => ['en' => ['name' => 'Political Science', 'description' => 'Teaching and research in political science.']]]);

        Program::query()->updateOrCreate(['slug' => 'droit-prive'], [
            'department_id' => $law->id,
            'title' => 'Droit Privé', 'level' => 'Licence · Master', 'domain' => 'Droit', 'mention' => 'Droit privé',
            'description' => 'Une formation consacrée aux relations entre les personnes, les entreprises et les institutions privées.',
            'objectives' => '<ul><li>Acquérir une solide culture juridique.</li><li>Maîtriser l’analyse et la rédaction juridiques.</li><li>Comprendre les procédures et les institutions.</li></ul>',
            'admission_requirements' => 'Les conditions sont précisées lors de chaque campagne d’admission.',
            'skills' => '<ul><li>Raisonnement juridique</li><li>Recherche documentaire</li><li>Argumentation et rédaction</li></ul>',
            'careers' => 'Professions juridiques, administration, entreprises et poursuite d’études.',
            'duration' => 'Licence et Master', 'curriculum' => 'Le détail officiel des unités d’enseignement est administrable depuis le CMS.',
            'status' => 'published', 'position' => 1, 'meta_title' => 'Formation Droit Privé — EDSP',
            'meta_description' => 'Découvrez le parcours de formation en droit privé proposé par l’EDSP.',
            'robots_index' => true, 'robots_follow' => true, 'published_at' => now(),
        ]);

        Program::query()->updateOrCreate(['slug' => 'science-politique'], [
            'department_id' => $politics->id,
            'title' => 'Science Politique', 'level' => 'Licence · Master', 'domain' => 'Science politique', 'mention' => 'Science politique',
            'description' => 'Une formation orientée vers l’analyse des institutions, de l’action publique, des politiques publiques et de la gouvernance.',
            'objectives' => '<ul><li>Analyser les institutions et les systèmes politiques.</li><li>Comprendre l’action publique.</li><li>Développer une lecture critique des transformations contemporaines.</li></ul>',
            'admission_requirements' => 'Les conditions sont précisées lors de chaque campagne d’admission.',
            'skills' => '<ul><li>Analyse institutionnelle</li><li>Conception et évaluation des politiques publiques</li><li>Recherche en sciences sociales</li></ul>',
            'careers' => 'Administration publique, collectivités, organisations, recherche et poursuite d’études.',
            'duration' => 'Licence et Master', 'curriculum' => 'Le détail officiel des unités d’enseignement est administrable depuis le CMS.',
            'status' => 'published', 'position' => 2, 'meta_title' => 'Formation Science Politique — EDSP',
            'meta_description' => 'Découvrez le parcours de formation en science politique proposé par l’EDSP.',
            'robots_index' => true, 'robots_follow' => true, 'published_at' => now(),
        ]);

        $translations = [
            'droit-prive' => [
                'title' => 'Private Law', 'level' => 'Bachelor’s · Master’s', 'domain' => 'Law', 'mention' => 'Private law',
                'description' => 'A programme focused on relationships between individuals, businesses and private institutions.',
                'objectives' => '<ul><li>Build a strong foundation in law.</li><li>Master legal analysis and drafting.</li><li>Understand legal procedures and institutions.</li></ul>',
                'admission_requirements' => 'Requirements are specified for each admission round.',
                'skills' => '<ul><li>Legal reasoning</li><li>Documentary research</li><li>Legal argument and drafting</li></ul>',
                'careers' => 'Legal professions, public administration, business and further study.', 'duration' => 'Bachelor’s and Master’s degrees',
                'curriculum' => 'The official course-unit catalogue is managed through the CMS.', 'meta_title' => 'Private Law programme — EDSP',
                'meta_description' => 'Discover EDSP’s Private Law degree programme.',
            ],
            'science-politique' => [
                'title' => 'Political Science', 'level' => 'Bachelor’s · Master’s', 'domain' => 'Political science', 'mention' => 'Political science',
                'description' => 'A programme focused on institutions, public action, public policy and governance.',
                'objectives' => '<ul><li>Analyse institutions and political systems.</li><li>Understand public action.</li><li>Develop a critical perspective on contemporary transformations.</li></ul>',
                'admission_requirements' => 'Requirements are specified for each admission round.',
                'skills' => '<ul><li>Institutional analysis</li><li>Public policy design and evaluation</li><li>Social science research</li></ul>',
                'careers' => 'Public administration, local authorities, organisations, research and further study.', 'duration' => 'Bachelor’s and Master’s degrees',
                'curriculum' => 'The official course-unit catalogue is managed through the CMS.', 'meta_title' => 'Political Science programme — EDSP',
                'meta_description' => 'Discover EDSP’s Political Science degree programme.',
            ],
        ];
        foreach ($translations as $slug => $fields) {
            Program::query()->where('slug', $slug)->first()?->update(['translations' => ['en' => $fields]]);
        }
    }
}
