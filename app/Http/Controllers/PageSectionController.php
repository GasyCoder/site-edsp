<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePageSectionRequest;
use App\Models\PageSection;
use App\Services\ContentRevisionService;

class PageSectionController extends Controller
{
    public function update(UpdatePageSectionRequest $request, PageSection $section, ContentRevisionService $revisions)
    {
        $revisions->update($section, $request->validated(), $request->user()->id);

        return redirect()->back(303)->with('success', 'Section mise à jour.');
    }
}
