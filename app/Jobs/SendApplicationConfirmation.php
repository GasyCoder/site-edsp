<?php

namespace App\Jobs;

use App\Mail\ApplicationSubmittedMail;
use App\Models\Application;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendApplicationConfirmation implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public readonly int $applicationId) {}

    public function handle(): void
    {
        $application = Application::query()->find($this->applicationId);
        if ($application) {
            Mail::to($application->email)->send(new ApplicationSubmittedMail($application));
        }
    }
}
