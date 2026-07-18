<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\SettingService;

class SettingController extends Controller
{
    public function update(UpdateSettingRequest $request, Setting $setting, SettingService $settings)
    {
        $this->authorize('update', $setting);
        $setting->update($request->validated());
        $settings->forget();

        return back()->with('success', 'Le paramètre a été mis à jour.');
    }
}
