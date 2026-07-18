<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Partner;
use App\Models\TeamMember;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class CommunitySeeder extends Seeder
{
    public function run(): void
    {
        $law = Department::query()->where('slug', 'droit')->first();
        $politics = Department::query()->where('slug', 'science-politique')->first();
        $members = [
            ['Nom', '[à renseigner]', 'Direction de l’EDSP', null],
            ['Nom', '[à renseigner]', 'Responsable administratif', null],
            ['Nom', '[à renseigner]', 'Responsable du parcours Droit privé', $law?->id],
            ['Nom', '[à renseigner]', 'Responsable du parcours Science politique', $politics?->id],
        ];
        foreach ($members as $index => [$firstName, $lastName, $position, $departmentId]) {
            TeamMember::query()->updateOrCreate(['position' => $position], [
                'first_name' => $firstName, 'last_name' => $lastName, 'department_id' => $departmentId,
                'biography' => 'Profil institutionnel à compléter et valider par l’établissement.',
                'display_order' => $index + 1, 'is_visible' => true, 'status' => 'published',
            ]);
        }

        $testimonials = [
            ['Miora R. (exemple)', 'Étudiante — témoignage de démonstration', 'Les enseignements nous poussent à raisonner avec méthode et à relier le droit aux réalités de notre société.'],
            ['Tojo A. (exemple)', 'Étudiant — témoignage de démonstration', 'Les débats et les conférences m’ont aidé à développer une lecture plus structurée des enjeux publics.'],
            ['Fanja L. (exemple)', 'Diplômée — témoignage de démonstration', 'Le parcours présenté ici illustre la manière dont une expérience étudiante peut être valorisée sur le site.'],
        ];
        foreach ($testimonials as [$name, $role, $content]) {
            Testimonial::query()->updateOrCreate(['author_name' => $name], ['author_role' => $role, 'content' => $content, 'is_visible' => true]);
        }

        Partner::query()->updateOrCreate(['name' => 'Partenaire institutionnel — à renseigner'], ['url' => null, 'position' => 1, 'is_visible' => false]);
    }
}
