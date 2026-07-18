<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContactMessageRequest;
use App\Jobs\SendContactNotification;
use App\Models\ContactMessage;

class ContactController extends Controller
{
    public function store(StoreContactMessageRequest $request)
    {
        $message = ContactMessage::create($request->safe()->except('website'));
        SendContactNotification::dispatch($message->id)->afterCommit();

        return back()->with('success', 'Votre message a bien été envoyé.');
    }
}
