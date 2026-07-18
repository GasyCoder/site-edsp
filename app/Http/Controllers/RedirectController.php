<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function __invoke(Request $request)
    {
        $redirect = Redirect::query()->where('source_path', '/'.ltrim($request->path(), '/'))->where('is_active', true)->first();
        abort_if($redirect === null, 404);
        abort_if($redirect->destination_url === $request->fullUrl() || $redirect->destination_url === $request->getPathInfo(), 404);

        $statusCode = in_array($redirect->status_code, [301, 302, 307, 308], true) ? $redirect->status_code : 301;

        return redirect()->to($redirect->destination_url, $statusCode);
    }
}
