<?php

namespace App\Http\Controllers;

use App\Actions\ChangeApplicationStatus;
use App\Enums\ApplicationStatus;
use App\Http\Requests\UpdateApplicationStatusRequest;
use App\Models\Application;

class ApplicationStatusController extends Controller
{
    public function update(UpdateApplicationStatusRequest $request, Application $application, ChangeApplicationStatus $changeStatus)
    {
        $this->authorize('changeStatus', $application);
        $changeStatus->handle(
            $application,
            ApplicationStatus::from($request->string('status')->toString()),
            $request->user()->id,
            $request->input('comment'),
        );

        return back()->with('success', 'Le statut du dossier a été mis à jour.');
    }
}
