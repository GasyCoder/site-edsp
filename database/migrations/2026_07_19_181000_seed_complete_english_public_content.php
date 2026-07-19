<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $settings = [
            ['institution_name_en', 'School of Law and Political Science', 'string', 'general'],
            ['site_description_en', 'University degree programmes in Law and Political Science in Mahajanga.', 'text', 'general'],
            ['footer_text_en', 'School of Law and Political Science', 'text', 'general'],
            ['default_meta_title_en', 'EDSP — School of Law and Political Science', 'string', 'seo'],
            ['default_meta_description_en', 'Explore programmes, news and admissions at the School of Law and Political Science.', 'text', 'seo'],
            ['default_meta_keywords_en', 'EDSP, law, political science, Bachelor, Master, Mahajanga, Madagascar', 'text', 'seo'],
        ];
        foreach ($settings as [$key, $value, $type, $group]) {
            DB::table('settings')->updateOrInsert(['key' => $key], [
                'value' => $value, 'type' => $type, 'group' => $group, 'is_public' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        $pageContent = [
            'presentation' => ['title' => 'About EDSP', 'content' => 'EDSP provides education for legal professionals and political science specialists within the University of Mahajanga.'],
            'historique' => ['title' => 'History', 'content' => 'The School’s official historical milestones can be managed and updated through the CMS.'],
            'missions-et-valeurs' => ['title' => 'Mission and values', 'content' => 'Academic excellence, critical thinking, public service and openness to society shape EDSP’s educational vision.'],
            'equipe' => ['title' => 'Leadership and team', 'content' => 'Meet EDSP’s leadership, programme coordinators and teaching staff.'],
            'admissions' => ['title' => 'Admissions', 'content' => 'View the steps, requirements and timetable for current admission rounds.'],
            'bibliotheque' => ['title' => 'Library', 'content' => 'The library provides documentary resources for study and research.'],
            'vie-etudiante' => ['title' => 'Student life', 'content' => 'Academic activities, conferences and student initiatives enrich the university experience.'],
            'galerie' => ['title' => 'Gallery', 'content' => 'Explore academic life and School events through photographs.'],
            'partenaires' => ['title' => 'Partners', 'content' => 'Discover the School’s approved institutional and academic partnerships.'],
            'contact' => ['title' => 'Contact', 'content' => 'Use the contact form to send your enquiry to EDSP.'],
            'mentions-legales' => ['title' => 'Legal notice', 'content' => 'The School’s official legal information must be completed before the website goes live.'],
            'politique-de-confidentialite' => ['title' => 'Privacy policy', 'content' => 'Submitted data is used solely to process enquiries and applications in accordance with applicable rules.'],
        ];
        foreach ($pageContent as $slug => $fields) {
            $pageId = DB::table('pages')->where('slug', $slug)->value('id');
            if ($pageId) {
                DB::table('page_sections')->where('page_id', $pageId)->where('section_key', 'main')->update([
                    'translations' => $this->json($fields),
                ]);
            }
        }

        DB::table('team_members')->orderBy('id')->get(['id', 'position'])->each(function ($member): void {
            $position = match ($member->position) {
                'Direction de l’EDSP' => 'EDSP Leadership',
                'Responsable administratif' => 'Administrative Manager',
                'Responsable du parcours Droit privé' => 'Private Law Programme Coordinator',
                'Responsable du parcours Science politique' => 'Political Science Programme Coordinator',
                default => $member->position,
            };
            DB::table('team_members')->where('id', $member->id)->update(['translations' => $this->json([
                'position' => $position,
                'biography' => 'Institutional profile to be completed and approved by the School.',
            ])]);
        });

        $testimonials = [
            'Miora R. (exemple)' => ['author_role' => 'Student — sample testimonial', 'content' => 'Our courses encourage us to think methodically and connect law with the realities of our society.'],
            'Tojo A. (exemple)' => ['author_role' => 'Student — sample testimonial', 'content' => 'Debates and conferences have helped me develop a more structured understanding of public issues.'],
            'Fanja L. (exemple)' => ['author_role' => 'Graduate — sample testimonial', 'content' => 'The pathway presented here shows how a student experience can be highlighted on the website.'],
        ];
        foreach ($testimonials as $author => $fields) {
            DB::table('testimonials')->where('author_name', $author)->update(['translations' => $this->json($fields)]);
        }

        DB::table('admission_campaigns')->get(['id', 'academic_year', 'required_documents'])->each(function ($campaign): void {
            $documents = collect(json_decode($campaign->required_documents ?: '[]', true))
                ->map(function (mixed $document): mixed {
                    $label = is_array($document) ? ($document['label'] ?? '') : $document;
                    $english = match (Str::lower((string) $label)) {
                        'pièce d’identité' => 'Identity document',
                        'diplôme ou attestation' => 'Diploma or certificate',
                        'relevé de notes' => 'Academic transcript',
                        "photo d'identité" => 'Passport photo',
                        default => $label,
                    };

                    return is_array($document) ? [...$document, 'label' => $english] : $english;
                })->all();
            DB::table('admission_campaigns')->where('id', $campaign->id)->update(['translations' => $this->json([
                'title' => 'Applications '.$campaign->academic_year,
                'instructions' => '<p>Complete the form carefully and review your information before submitting it. Official requirements published by the School take precedence.</p>',
                'required_documents' => $documents,
            ])]);
        });

        Cache::forget('settings.public');
    }

    public function down(): void
    {
        foreach (['institution_name_en', 'site_description_en', 'footer_text_en', 'default_meta_title_en', 'default_meta_description_en', 'default_meta_keywords_en'] as $key) {
            DB::table('settings')->where('key', $key)->delete();
        }
        Cache::forget('settings.public');
    }

    /** @param array<string, mixed> $fields */
    private function json(array $fields): string
    {
        return json_encode(['en' => $fields], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
};
