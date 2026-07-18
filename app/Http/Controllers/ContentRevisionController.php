<?php

namespace App\Http\Controllers;

use App\Models\ContentRevision;
use App\Services\ContentRevisionService;
use Illuminate\Http\Request;

class ContentRevisionController extends Controller
{
    public function restore(Request $request, ContentRevision $revision, ContentRevisionService $revisions)
    {
        $this->authorize('restore', $revision);
        $revisions->restore($revision, $request->user()->id);

        return back()->with('success', 'La révision a été restaurée.');
    }
}
