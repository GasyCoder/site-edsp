<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\AdmissionCampaign;
use App\Models\ParcoursLevel;
use App\Services\ApplicationService;
use App\Services\SeoService;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ApplicationController extends Controller
{
    public function create(SeoService $seo)
    {
        $campaign = AdmissionCampaign::open()->with('programs')->first();

        if ($campaign !== null) {
            $campaign->setAttribute('required_documents', $campaign->normalizedRequiredDocuments());
        }

        return Inertia::render('Admissions', [
            'campaign' => $campaign,
            'academicOptions' => ParcoursLevel::query()
                ->where('is_active', true)
                ->with(['level', 'parcours.mention'])
                ->get()
                ->filter(fn (ParcoursLevel $link): bool => $link->level !== null
                    && $link->parcours !== null
                    && $link->parcours->mention !== null
                    && $link->parcours->mention->is_active)
                ->sortBy(fn (ParcoursLevel $link): array => [
                    $link->level->ordre,
                    $link->parcours->mention->nom,
                    $link->parcours->nom,
                ])
                ->values()
                ->map(fn (ParcoursLevel $link): array => [
                    'level_id' => $link->level_id,
                    'level_code' => $link->level->code,
                    'level_name' => $link->level->nom,
                    'mention_id' => $link->parcours->mention_id,
                    'mention_code' => $link->parcours->mention->code,
                    'mention_name' => $link->parcours->mention->nom,
                    'parcours_id' => $link->parcours_id,
                    'parcours_name' => $link->parcours->nom,
                ]),
            'seo' => $seo->forListing('Inscription — EDSP', 'Déposez votre demande d’inscription auprès de l’École de Droit et Science Politique.'),
        ]);
    }

    public function store(StoreApplicationRequest $request, ApplicationService $service)
    {
        $campaign = AdmissionCampaign::open()->findOrFail($request->integer('admission_campaign_id'));
        abort_unless($campaign->programs()->whereKey($request->integer('program_id'))->exists(), 422);
        $files = $request->file('documents', []);
        $requiredDocuments = collect($campaign->normalizedRequiredDocuments());
        $missing = $requiredDocuments->filter(fn (array $document) => $document['required'] && ! isset($files[$document['key']]));
        if ($missing->isNotEmpty()) {
            throw ValidationException::withMessages($missing->mapWithKeys(fn (array $document) => ['documents.'.($document['key'] ?? 'file') => 'Le document « '.($document['label'] ?? 'demandé').' » est requis.'])->all());
        }
        $data = $request->safe()->except('website');
        $data['academic_background'] = filled($data['academic_background'] ?? null)
            ? $data['academic_background']
            : trim($data['last_diploma'].' — '.$data['previous_institution'].' ('.$data['graduation_year'].')');
        $application = $service->create($data);

        return back()->with(
            'success',
            "Votre dossier {$application->application_number} a été enregistré. Un e-mail de confirmation va être envoyé à {$application->email}.",
        );
    }
}
