<?php

namespace Database\Seeders;

use App\Models\Mention;
use App\Models\Program;
use Illuminate\Database\Seeder;

class AcademicContentSeeder extends Seeder
{
    public function run(): void
    {
        $law = Mention::query()->where('code', 'DROIT')->firstOrFail();
        $politics = Mention::query()->whereIn('code', ['SCPO', 'SP', 'SCIENCE-POLITIQUE'])->firstOrFail();

        $lawProgram = Program::query()->updateOrCreate(['slug' => 'droit-prive'], [
            'mention_id' => $law->id,
            'department_id' => null,
            'title' => 'Droit', 'level' => 'L1 à M2', 'domain' => 'Droit', 'mention' => 'Droit',
            'description' => 'Une formation juridique progressive, du tronc commun de Licence aux spécialisations en droit privé et droit des affaires.',
            'objectives' => '<ul><li>Acquérir une solide culture juridique.</li><li>Maîtriser l’analyse et la rédaction juridiques.</li><li>Comprendre les procédures et les institutions.</li></ul>',
            'admission_requirements' => 'Les conditions sont précisées lors de chaque campagne d’admission.',
            'skills' => '<ul><li>Raisonnement juridique</li><li>Recherche documentaire</li><li>Argumentation et rédaction</li></ul>',
            'careers' => 'Professions juridiques, administration, entreprises et poursuite d’études.',
            'duration' => 'Licence et Master', 'curriculum' => 'Le détail officiel des unités d’enseignement est administrable depuis le CMS.',
            'status' => 'published', 'position' => 1, 'meta_title' => 'Mention Droit : parcours de Licence et Master | EDSP',
            'meta_description' => 'Découvrez la mention Droit de l’EDSP : tronc commun, Droit privé et Droit des affaires, de la L1 au M2.',
            'robots_index' => true, 'robots_follow' => true, 'published_at' => now(),
        ]);

        $politicsProgram = Program::query()->updateOrCreate(['slug' => 'science-politique'], [
            'mention_id' => $politics->id,
            'department_id' => null,
            'title' => 'Sciences Politiques', 'level' => 'L1 à M2', 'domain' => 'Sciences Politiques', 'mention' => 'Sciences Politiques',
            'description' => 'Une formation en sciences politiques allant des fondements de Licence à la spécialisation en études politiques au niveau Master.',
            'objectives' => '<ul><li>Analyser les institutions et les systèmes politiques.</li><li>Comprendre l’action publique.</li><li>Développer une lecture critique des transformations contemporaines.</li></ul>',
            'admission_requirements' => 'Les conditions sont précisées lors de chaque campagne d’admission.',
            'skills' => '<ul><li>Analyse institutionnelle</li><li>Conception et évaluation des politiques publiques</li><li>Recherche en sciences sociales</li></ul>',
            'careers' => 'Administration publique, collectivités, organisations, recherche et poursuite d’études.',
            'duration' => 'Licence et Master', 'curriculum' => 'Le détail officiel des unités d’enseignement est administrable depuis le CMS.',
            'status' => 'published', 'position' => 2, 'meta_title' => 'Mention Sciences Politiques : Licence et Master | EDSP',
            'meta_description' => 'Découvrez les parcours Science Politique et Études Politiques proposés par l’EDSP de la L1 au M2.',
            'robots_index' => true, 'robots_follow' => true, 'published_at' => now(),
        ]);

        $lawProgram->parcoursLevels()->sync(
            $law->parcours()->with('levelLinks')->get()->pluck('levelLinks')->flatten()->pluck('id'),
        );
        $politicsProgram->parcoursLevels()->sync(
            $politics->parcours()->with('levelLinks')->get()->pluck('levelLinks')->flatten()->pluck('id'),
        );

        $translations = [
            'droit-prive' => [
                'title' => 'Law', 'level' => 'L1 to M2', 'domain' => 'Law', 'mention' => 'Law',
                'description' => 'A progressive legal education from the undergraduate common core to specialisations in Private Law and Business Law.',
                'objectives' => '<ul><li>Build a strong foundation in law.</li><li>Master legal analysis and drafting.</li><li>Understand legal procedures and institutions.</li></ul>',
                'admission_requirements' => 'Requirements are specified for each admission round.',
                'skills' => '<ul><li>Legal reasoning</li><li>Documentary research</li><li>Legal argument and drafting</li></ul>',
                'careers' => 'Legal professions, public administration, business and further study.', 'duration' => 'Bachelor’s and Master’s degrees',
                'curriculum' => 'The official course-unit catalogue is managed through the CMS.', 'meta_title' => 'Law degree pathways: Bachelor to Master | EDSP',
                'meta_description' => 'Explore EDSP Law pathways from the common core through Private Law and Business Law, from L1 to M2.',
            ],
            'science-politique' => [
                'title' => 'Political Science', 'level' => 'Bachelor’s · Master’s', 'domain' => 'Political science', 'mention' => 'Political science',
                'description' => 'A Political Science education spanning undergraduate foundations and advanced Political Studies at Master level.',
                'objectives' => '<ul><li>Analyse institutions and political systems.</li><li>Understand public action.</li><li>Develop a critical perspective on contemporary transformations.</li></ul>',
                'admission_requirements' => 'Requirements are specified for each admission round.',
                'skills' => '<ul><li>Institutional analysis</li><li>Public policy design and evaluation</li><li>Social science research</li></ul>',
                'careers' => 'Public administration, local authorities, organisations, research and further study.', 'duration' => 'Bachelor’s and Master’s degrees',
                'curriculum' => 'The official course-unit catalogue is managed through the CMS.', 'meta_title' => 'Political Science pathways: Bachelor to Master | EDSP',
                'meta_description' => 'Explore EDSP Political Science and Political Studies pathways from L1 to M2.',
            ],
        ];
        foreach ($translations as $slug => $fields) {
            Program::query()->where('slug', $slug)->first()?->update(['translations' => ['en' => $fields]]);
        }
    }
}
