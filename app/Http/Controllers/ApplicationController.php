<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\AdmissionCampaign;
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
            'seo' => $seo->forListing('Préinscription — EDSP', 'Déposez votre demande de préinscription auprès de l’École de Droit et Science Politique.'),
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
        $application = $service->create($request->safe()->except('website'));

        return back()->with('success', "Votre dossier {$application->application_number} a été enregistré.");
    }
}
