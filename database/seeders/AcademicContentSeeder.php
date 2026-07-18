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
    }
}
