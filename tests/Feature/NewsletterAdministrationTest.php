<?php

use App\Filament\Resources\NewsletterCampaigns\Pages\CreateNewsletterCampaign;
use App\Filament\Resources\NewsletterCampaigns\Pages\ListNewsletterCampaigns;
use App\Filament\Resources\NewsletterSubscribers\Pages\ManageNewsletterSubscribers;
use App\Jobs\SendNewsletterDelivery;
use App\Mail\NewsletterCampaignMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterSubscriber;
use App\Services\DispatchNewsletterCampaign;
use App\Services\NewsletterCampaignLifecycle;
use App\Services\NewsletterSubscriberImporter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('administrators import subscribers individually or in bulk without duplicates', function (): void {
    $result = app(NewsletterSubscriberImporter::class)->fromText(<<<'CSV'
email;nom
etudiant@example.test;Aina Rakoto
INVALID;Adresse invalide
etudiant@example.test;Aina R.
CSV);

    expect($result)->toBe(['created' => 1, 'updated' => 1, 'invalid' => 1]);

    $subscriber = NewsletterSubscriber::query()->sole();
    expect($subscriber->email)->toBe('etudiant@example.test')
        ->and($subscriber->name)->toBe('Aina R.')
        ->and($subscriber->source)->toBe('import')
        ->and($subscriber->subscription_status)->toBe('active');
});

test('a campaign queues only verified and currently subscribed recipients', function (): void {
    Queue::fake();
    $active = NewsletterSubscriber::query()->create([
        'email' => 'active@example.test',
        'source' => 'website',
        'verified_at' => now(),
    ]);
    NewsletterSubscriber::query()->create([
        'email' => 'pending@example.test',
        'source' => 'website',
    ]);
    NewsletterSubscriber::query()->create([
        'email' => 'unsubscribed@example.test',
        'source' => 'website',
        'verified_at' => now()->subDay(),
        'unsubscribed_at' => now(),
    ]);
    $campaign = NewsletterCampaign::query()->create([
        'type' => 'message',
        'title' => 'Information importante',
        'subject' => 'Information importante — EDSP',
        'content' => '<p>Contenu du message.</p>',
        'status' => 'draft',
    ]);

    app(DispatchNewsletterCampaign::class)->handle($campaign);

    expect($campaign->fresh()->status)->toBe('sending')
        ->and($campaign->fresh()->recipient_count)->toBe(1);
    $this->assertDatabaseHas('newsletter_deliveries', [
        'newsletter_campaign_id' => $campaign->id,
        'newsletter_subscriber_id' => $active->id,
        'status' => 'queued',
    ]);
    $this->assertDatabaseCount('newsletter_deliveries', 1);
    Queue::assertPushed(SendNewsletterDelivery::class, 1);
});

test('a newsletter delivery is individualized and completes campaign statistics', function (): void {
    Mail::fake();
    $subscriber = NewsletterSubscriber::query()->create([
        'email' => 'destinataire@example.test',
        'name' => 'Fara',
        'source' => 'website',
        'verified_at' => now(),
    ]);
    $campaign = NewsletterCampaign::query()->create([
        'type' => 'event',
        'title' => 'Journée portes ouvertes',
        'subject' => 'Invitation EDSP',
        'content' => '<p>Nous vous invitons.</p>',
        'event_starts_at' => now()->addWeek(),
        'status' => 'sending',
        'recipient_count' => 1,
    ]);
    $delivery = NewsletterDelivery::query()->create([
        'newsletter_campaign_id' => $campaign->id,
        'newsletter_subscriber_id' => $subscriber->id,
        'status' => 'queued',
    ]);

    (new SendNewsletterDelivery($delivery->id))->handle();

    Mail::assertSent(NewsletterCampaignMail::class, fn (NewsletterCampaignMail $mail): bool => $mail->hasTo('destinataire@example.test')
        && str_contains($mail->unsubscribeUrl, '/newsletter/desinscription/'.$subscriber->id));
    expect($delivery->fresh()->status)->toBe('sent')
        ->and($campaign->fresh()->status)->toBe('sent')
        ->and($campaign->fresh()->delivered_count)->toBe(1);
});

test('an attached image is displayed directly inside the institutional email', function (): void {
    Storage::fake('private');
    $image = UploadedFile::fake()->image('campus-edsp.jpg', 1200, 630);
    Storage::disk('private')->putFileAs('newsletter/attachments', $image, 'campus-edsp.jpg');
    $subscriber = NewsletterSubscriber::query()->create([
        'email' => 'image@example.test',
        'name' => 'Aina',
        'source' => 'website',
        'verified_at' => now(),
    ]);
    $campaign = NewsletterCampaign::query()->create([
        'type' => 'news',
        'title' => 'La vie à l’EDSP',
        'subject' => 'La vie à l’EDSP',
        'preheader' => 'Découvrez les activités de l’établissement.',
        'content' => '<p>Une actualité institutionnelle.</p>',
        'external_url' => 'https://edsp.example.test/actualites',
        'external_url_label' => 'Lire l’actualité',
        'attachment_disk' => 'private',
        'attachment_path' => 'newsletter/attachments/campus-edsp.jpg',
        'attachment_name' => 'campus-edsp.jpg',
        'status' => 'draft',
    ]);

    $html = (new NewsletterCampaignMail($campaign, $subscriber, 'https://edsp.example.test/desinscription'))->render();

    expect($html)->toContain('Illustration jointe')
        ->toContain('campus-edsp.jpg')
        ->toContain('Lire l’actualité')
        ->toContain('data:image/jpeg;base64,');
});

test('a subscriber can unsubscribe with the signed link included in messages', function (): void {
    $subscriber = NewsletterSubscriber::query()->create([
        'email' => 'stop@example.test',
        'source' => 'website',
        'verified_at' => now(),
    ]);
    $url = URL::temporarySignedRoute('newsletter.unsubscribe', now()->addHour(), ['subscriber' => $subscriber]);

    $this->get($url)
        ->assertRedirect('/#newsletter')
        ->assertSessionHas('newsletter', fn (array $feedback): bool => str_contains($feedback['message'], 'désinscrite'));

    expect($subscriber->fresh()->unsubscribed_at)->not->toBeNull();
});

test('newsletter administration pages expose composition import and export tools', function (): void {
    $this->actingAs(userWithPermissions([
        'access admin',
        'view newsletter subscribers',
        'create newsletter subscribers',
        'edit newsletter subscribers',
        'delete newsletter subscribers',
        'import newsletter subscribers',
        'export newsletter subscribers',
        'view newsletter campaigns',
        'create newsletter campaigns',
        'edit newsletter campaigns',
        'delete newsletter campaigns',
        'send newsletter campaigns',
    ]));

    $this->get('/admin/newsletter-subscribers')
        ->assertOk()
        ->assertSee('Abonnés à la newsletter');
    $this->get('/admin/newsletter-campaigns/create')
        ->assertOk()
        ->assertSee('Créer une campagne newsletter')
        ->assertSee('Pièce jointe');

    Livewire::test(ManageNewsletterSubscribers::class)
        ->assertTableHeaderActionsExistInOrder(['import', 'export']);

    Livewire::test(CreateNewsletterCampaign::class)
        ->assertFormFieldExists('type')
        ->assertFormFieldExists('subject')
        ->assertFormFieldExists('content')
        ->assertFormFieldExists('attachment_path');

    Livewire::test(ListNewsletterCampaigns::class)
        ->assertTableActionExists('pause')
        ->assertTableActionExists('resume')
        ->assertTableActionExists('reopen')
        ->assertTableActionExists('cancel')
        ->assertTableActionExists('announce_event_cancellation')
        ->assertTableBulkActionsExistInOrder([
            'pause_selected',
            'resume_selected',
            'reopen_selected',
            'cancel_selected',
            'delete',
        ]);
});

test('only a super administrator can manually confirm a pending subscriber', function (): void {
    $subscriber = NewsletterSubscriber::query()->create([
        'email' => 'confirmation@example.test',
        'source' => 'website',
    ]);
    $manager = userWithPermissions([
        'access admin',
        'view newsletter subscribers',
        'edit newsletter subscribers',
    ]);

    $this->actingAs($manager);

    Livewire::test(ManageNewsletterSubscribers::class)
        ->assertTableActionHidden('confirm_email', $subscriber);

    $superAdministrator = userWithPermissions(['access admin']);
    $superAdministrator->assignRole(Role::findOrCreate('superadmin', 'web'));
    $this->actingAs($superAdministrator);

    Livewire::test(ManageNewsletterSubscribers::class)
        ->assertTableActionVisible('confirm_email', $subscriber)
        ->callTableAction('confirm_email', $subscriber);

    expect($subscriber->fresh()->subscription_status)->toBe('active')
        ->and($subscriber->fresh()->verified_at)->not->toBeNull();
});

test('a super administrator can confirm multiple pending subscribers at once', function (): void {
    $pendingSubscribers = new Collection([
        NewsletterSubscriber::query()->create(['email' => 'pending-one@example.test', 'source' => 'website']),
        NewsletterSubscriber::query()->create(['email' => 'pending-two@example.test', 'source' => 'website']),
    ]);
    $activeSubscriber = NewsletterSubscriber::query()->create([
        'email' => 'already-active@example.test',
        'source' => 'website',
        'verified_at' => now()->subDay(),
    ]);
    $superAdministrator = userWithPermissions(['access admin']);
    $superAdministrator->assignRole(Role::findOrCreate('superadmin', 'web'));

    $this->actingAs($superAdministrator);

    Livewire::test(ManageNewsletterSubscribers::class)
        ->assertTableBulkActionVisible('confirm_selected')
        ->callTableBulkAction('confirm_selected', $pendingSubscribers->push($activeSubscriber));

    expect($pendingSubscribers->every(
        fn (NewsletterSubscriber $subscriber): bool => $subscriber->fresh()->subscription_status === 'active',
    ))->toBeTrue()
        ->and($activeSubscriber->fresh()->verified_at->equalTo($activeSubscriber->verified_at))->toBeTrue();
});

test('a scheduled newsletter can be suspended and resumed without being dispatched', function (): void {
    $actor = userWithPermissions(['send newsletter campaigns']);
    $campaign = NewsletterCampaign::query()->create([
        'type' => 'message',
        'title' => 'Message programmé',
        'subject' => 'Message programmé',
        'content' => '<p>Contenu.</p>',
        'status' => 'scheduled',
        'scheduled_at' => now()->addHour(),
    ]);
    $lifecycle = app(NewsletterCampaignLifecycle::class);

    $lifecycle->pause($campaign, 'Vérification institutionnelle en cours.', $actor->id);

    expect($campaign->fresh()->status)->toBe('paused')
        ->and($campaign->fresh()->status_reason)->toBe('Vérification institutionnelle en cours.')
        ->and(NewsletterCampaign::query()->due()->whereKey($campaign)->exists())->toBeFalse();

    $lifecycle->resume($campaign, $actor->id);

    expect($campaign->fresh()->status)->toBe('scheduled')
        ->and($campaign->fresh()->paused_at)->toBeNull()
        ->and($campaign->fresh()->status_reason)->toBeNull();
});

test('cancelling a sending campaign prevents queued deliveries from being sent', function (): void {
    Mail::fake();
    $actor = userWithPermissions(['send newsletter campaigns']);
    $subscriber = NewsletterSubscriber::query()->create([
        'email' => 'cancelled-delivery@example.test',
        'source' => 'website',
        'verified_at' => now(),
    ]);
    $campaign = NewsletterCampaign::query()->create([
        'type' => 'message',
        'title' => 'Message à annuler',
        'subject' => 'Message à annuler',
        'content' => '<p>Contenu.</p>',
        'status' => 'sending',
        'recipient_count' => 1,
    ]);
    $delivery = NewsletterDelivery::query()->create([
        'newsletter_campaign_id' => $campaign->id,
        'newsletter_subscriber_id' => $subscriber->id,
        'status' => 'queued',
    ]);

    app(NewsletterCampaignLifecycle::class)->cancel(
        $campaign,
        'Information devenue obsolète.',
        $actor->id,
    );
    (new SendNewsletterDelivery($delivery->id))->handle();

    Mail::assertNothingSent();
    expect($campaign->fresh()->status)->toBe('cancelled')
        ->and($campaign->fresh()->status_reason)->toBe('Information devenue obsolète.')
        ->and($delivery->fresh()->status)->toBe('skipped');
});

test('cancelling an already announced event creates a separate cancellation notice', function (): void {
    $actor = userWithPermissions(['send newsletter campaigns']);
    $event = NewsletterCampaign::query()->create([
        'type' => 'event',
        'title' => 'Conférence EDSP',
        'subject' => 'Invitation à la conférence EDSP',
        'content' => '<p>Invitation.</p>',
        'event_starts_at' => now()->addWeek(),
        'event_location' => 'Amphithéâtre EDSP',
        'status' => 'sent',
        'sent_at' => now()->subHour(),
    ]);

    $notice = app(NewsletterCampaignLifecycle::class)->createEventCancellationNotice(
        $event,
        'La conférence est reportée à une date ultérieure.',
        $actor->id,
    );

    expect($event->fresh()->status)->toBe('event_cancelled')
        ->and($event->fresh()->status_reason)->toContain('reportée')
        ->and($notice->type)->toBe('event_cancellation')
        ->and($notice->status)->toBe('draft')
        ->and($notice->subject)->toStartWith('Annulation :')
        ->and($notice->content)->toContain('date ultérieure');
});
