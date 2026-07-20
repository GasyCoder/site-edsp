<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateInstitutionalReferenceRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Support\Facades\DB;

class SettingController extends Controller
{
    public function updateInstitutionalReference(UpdateInstitutionalReferenceRequest $request, SettingService $settings)
    {
        DB::transaction(function () use ($request): void {
            foreach ($request->validated() as $key => $value) {
                Setting::query()->updateOrCreate(['key' => $key], [
                    'value' => $value,
                    'type' => 'string',
                    'group' => 'legal',
                    'is_public' => true,
                ]);
            }
        });
        $settings->forget();

        return back()->with('success', 'Référence ministérielle mise à jour.');
    }

    public function update(UpdateSettingRequest $request, Setting $setting, SettingService $settings)
    {
        $this->authorize('update', $setting);
        $setting->update($request->validated());
        $settings->forget();

        return back()->with('success', 'Le paramètre a été mis à jour.');
    }
}
