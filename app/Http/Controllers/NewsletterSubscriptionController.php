<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNewsletterSubscriptionRequest;
use App\Jobs\SendNewsletterVerification;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NewsletterSubscriptionController extends Controller
{
    public function store(StoreNewsletterSubscriptionRequest $request): RedirectResponse
    {
        $email = (string) $request->validated('email');
        $subscriber = NewsletterSubscriber::query()->firstOrNew(['email' => $email]);

        if ($subscriber->exists && $subscriber->verified_at !== null && $subscriber->unsubscribed_at === null) {
            return back()->with('newsletter', [
                'status' => 'verified',
                'message' => 'Cette adresse e-mail est déjà confirmée et inscrite à la newsletter.',
            ]);
        }

        $subscriber->forceFill([
            'email' => $email,
            'verification_sent_at' => now(),
            'verified_at' => $subscriber->unsubscribed_at !== null ? null : $subscriber->verified_at,
            'unsubscribed_at' => null,
        ])->save();

        SendNewsletterVerification::dispatchSync($subscriber->id);

        return back()->with('newsletter', [
            'status' => 'pending',
            'message' => 'Un e-mail de confirmation vient de vous être envoyé. Cliquez sur le lien reçu pour valider votre inscription.',
        ]);
    }

    public function verify(Request $request, NewsletterSubscriber $subscriber): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect('/#newsletter')->with('newsletter', [
                'status' => 'error',
                'message' => 'Ce lien de confirmation est invalide ou a expiré. Saisissez de nouveau votre adresse pour recevoir un nouveau lien.',
            ]);
        }

        $subscriber->forceFill([
            'verified_at' => $subscriber->verified_at ?? now(),
            'unsubscribed_at' => null,
        ])->save();

        return redirect('/#newsletter')->with('newsletter', [
            'status' => 'verified',
            'message' => 'Votre adresse e-mail est confirmée. Vous êtes maintenant inscrit(e) à la newsletter de l’EDSP.',
        ]);
    }

    public function unsubscribe(Request $request, NewsletterSubscriber $subscriber): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect('/#newsletter')->with('newsletter', [
                'status' => 'error',
                'message' => 'Ce lien de désinscription est invalide ou a expiré.',
            ]);
        }

        $subscriber->forceFill(['unsubscribed_at' => now()])->save();

        return redirect('/#newsletter')->with('newsletter', [
            'status' => 'verified',
            'message' => 'Votre adresse a bien été désinscrite de la newsletter de l’EDSP.',
        ]);
    }
}
