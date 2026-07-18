<?php

use App\Enums\ApplicationStatus;
use App\Jobs\SendApplicationConfirmation;
use App\Jobs\SendApplicationStatusNotification;
use App\Jobs\SendContactNotification;
use App\Models\AdmissionCampaign;
use App\Models\Application;
use App\Models\Program;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

function openCampaignForTest(): array
{
    $program = Program::query()->create([
        'title' => 'Droit privé',
        'slug' => 'droit-prive',
        'level' => 'Licence',
        'description' => 'Formation juridique',
        'status' => 'published',
        'published_at' => now(),
    ]);
    $campaign = AdmissionCampaign::query()->create([
        'title' => 'Campagne de test',
        'academic_year' => '2026-2027',
        'opens_at' => now()->subDay(),
        'closes_at' => now()->addDay(),
        'required_documents' => [],
        'status' => 'published',
        'is_visible' => true,
    ]);
    $campaign->programs()->attach($program);

    return [$campaign, $program];
}

test('a candidate submits a private application with a unique number', function (): void {
    Storage::fake('private');
    Queue::fake();
    [$campaign, $program] = openCampaignForTest();

    $this->post(route('applications.store'), [
        'admission_campaign_id' => $campaign->id,
        'program_id' => $program->id,
        'first_name' => 'Aina',
        'last_name' => 'Rakoto',
        'email' => 'aina@example.test',
        'phone' => '+261 34 00 000 00',
        'birth_date' => '2000-01-10',
        'address' => 'Mahajanga',
        'academic_background' => 'Baccalauréat',
        'privacy_accepted' => '1',
        'documents' => [
            'identity' => UploadedFile::fake()->create('identite.pdf', 120, 'application/pdf'),
        ],
        'website' => '',
    ])->assertRedirect();

    $application = Application::query()->sole();
    expect($application->application_number)->toMatch('/^EDSP-\d{4}-[A-Z0-9]{8}$/')
        ->and($application->status)->toBe(ApplicationStatus::Submitted)
        ->and($application->documents)->toHaveCount(1)
        ->and($application->statusHistory)->toHaveCount(1);

    Storage::disk('private')->assertExists($application->documents->first()->path);
    Queue::assertPushed(SendApplicationConfirmation::class);
});

test('an admissions manager changes status with a complete history', function (): void {
    Queue::fake();
    [$campaign, $program] = openCampaignForTest();
    $application = Application::query()->create([
        'public_id' => fake()->uuid(),
        'application_number' => 'EDSP-2026-TEST0001',
        'admission_campaign_id' => $campaign->id,
        'program_id' => $program->id,
        'first_name' => 'Fara',
        'last_name' => 'Rabe',
        'email' => 'fara@example.test',
        'phone' => '+261 34 00 000 01',
        'birth_date' => '2001-02-02',
        'address' => 'Mahajanga',
        'academic_background' => 'Licence',
        'status' => 'submitted',
        'privacy_accepted' => true,
        'submitted_at' => now(),
    ]);
    $manager = userWithPermissions(['change application status']);

    $this->actingAs($manager)->patch(route('applications.status.update', $application), [
        'status' => 'accepted',
        'comment' => 'Dossier complet.',
    ])->assertRedirect();

    expect($application->fresh()->status)->toBe(ApplicationStatus::Accepted);
    $this->assertDatabaseHas('application_status_histories', [
        'application_id' => $application->id,
        'old_status' => 'submitted',
        'new_status' => 'accepted',
        'changed_by' => $manager->id,
    ]);
    Queue::assertPushed(SendApplicationStatusNotification::class);
});

test('the contact form stores consent and queues a notification', function (): void {
    Queue::fake();

    $this->post(route('contact.store'), [
        'first_name' => 'Lova',
        'last_name' => 'Rasoa',
        'email' => 'lova@example.test',
        'subject' => 'Demande d’information',
        'message' => 'Bonjour, je souhaite obtenir des renseignements complémentaires.',
        'consent' => '1',
        'website' => '',
    ])->assertRedirect();

    $this->assertDatabaseHas('contact_messages', [
        'email' => 'lova@example.test',
        'consent' => true,
    ]);
    Queue::assertPushed(SendContactNotification::class);
});

test('the honeypot rejects automated contact submissions', function (): void {
    $this->from('/contact')->post(route('contact.store'), [
        'last_name' => 'Robot',
        'email' => 'robot@example.test',
        'subject' => 'Spam',
        'message' => 'Message automatisé suffisamment long.',
        'consent' => '1',
        'website' => 'https://spam.example',
    ])->assertRedirect('/contact')->assertSessionHasErrors('website');

    $this->assertDatabaseCount('contact_messages', 0);
});
