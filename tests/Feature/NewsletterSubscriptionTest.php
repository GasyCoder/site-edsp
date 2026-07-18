<?php

use App\Jobs\SendNewsletterVerification;
use App\Mail\NewsletterVerificationMail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

test('a visitor requests a newsletter subscription and receives a verification email', function (): void {
    Mail::fake();

    $this->from('/')->post(route('newsletter.store'), [
        'email' => '  ETUDIANT@EXAMPLE.TEST ',
        'website' => '',
    ])->assertRedirect('/')
        ->assertSessionHas('newsletter', fn (array $feedback): bool => $feedback['status'] === 'pending');

    $this->assertDatabaseHas('newsletter_subscribers', [
        'email' => 'etudiant@example.test',
        'verified_at' => null,
    ]);

    $subscriber = NewsletterSubscriber::query()->sole();
    Mail::assertSent(
        NewsletterVerificationMail::class,
        fn (NewsletterVerificationMail $mail): bool => $mail->hasTo($subscriber->email),
    );
});

test('the verification job sends a temporary signed link', function (): void {
    Mail::fake();

    $subscriber = NewsletterSubscriber::query()->create([
        'email' => 'fara@example.test',
        'verification_sent_at' => now(),
    ]);

    (new SendNewsletterVerification($subscriber->id))->handle();

    Mail::assertSent(NewsletterVerificationMail::class, function (NewsletterVerificationMail $mail) use ($subscriber): bool {
        $request = Request::create($mail->verificationUrl);

        return $mail->hasTo($subscriber->email)
            && $request->hasValidSignature()
            && str_contains($mail->verificationUrl, '/newsletter/confirmer/'.$subscriber->id);
    });
});

test('a subscriber confirms the newsletter through the signed link', function (): void {
    $subscriber = NewsletterSubscriber::query()->create([
        'email' => 'lova@example.test',
        'verification_sent_at' => now(),
    ]);
    $url = URL::temporarySignedRoute(
        'newsletter.verify',
        now()->addHour(),
        ['subscriber' => $subscriber],
    );

    $this->get($url)
        ->assertRedirect('/#newsletter')
        ->assertSessionHas('newsletter', fn (array $feedback): bool => $feedback['status'] === 'verified');

    expect($subscriber->fresh()->verified_at)->not->toBeNull();
});

test('an invalid newsletter link never verifies the address', function (): void {
    $subscriber = NewsletterSubscriber::query()->create([
        'email' => 'invalid@example.test',
        'verification_sent_at' => now(),
    ]);

    $this->get(route('newsletter.verify', $subscriber))
        ->assertRedirect('/#newsletter')
        ->assertSessionHas('newsletter', fn (array $feedback): bool => $feedback['status'] === 'error');

    expect($subscriber->fresh()->verified_at)->toBeNull();
});

test('an already confirmed address is not emailed again', function (): void {
    Mail::fake();
    NewsletterSubscriber::query()->create([
        'email' => 'confirmed@example.test',
        'verification_sent_at' => now()->subDay(),
        'verified_at' => now()->subHour(),
    ]);

    $this->from('/')->post(route('newsletter.store'), [
        'email' => 'confirmed@example.test',
        'website' => '',
    ])->assertRedirect('/')
        ->assertSessionHas('newsletter', fn (array $feedback): bool => $feedback['status'] === 'verified');

    Mail::assertNothingSent();
});

test('the newsletter honeypot rejects automated subscriptions', function (): void {
    Mail::fake();

    $this->from('/')->post(route('newsletter.store'), [
        'email' => 'robot@example.test',
        'website' => 'https://spam.example',
    ])->assertRedirect('/')
        ->assertSessionHasErrors('website');

    $this->assertDatabaseCount('newsletter_subscribers', 0);
    Mail::assertNothingSent();
});
