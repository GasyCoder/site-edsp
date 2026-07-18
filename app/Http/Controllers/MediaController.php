<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMediaRequest;
use App\Http\Requests\UploadMediaRequest;
use App\Models\Media;
use App\Services\ActivityLogger;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Media::class);
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type' => ['nullable', Rule::in(['image', 'document'])],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $media = Media::query()
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($query) => $query->where('original_name', 'like', "%{$search}%")->orWhere('alt_text', 'like', "%{$search}%")))
            ->when(($filters['type'] ?? null) === 'image', fn ($query) => $query
                ->where('mime_type', 'like', 'image/%')
                ->whereNotNull('alt_text')
                ->where('alt_text', '!=', ''))
            ->when(($filters['type'] ?? null) === 'document', fn ($query) => $query->where('mime_type', 'not like', 'image/%'))
            ->latest()->paginate(24)->withQueryString();

        return response()->json($media);
    }

    public function store(UploadMediaRequest $request, MediaService $service): JsonResponse
    {
        $media = $service->upload($request->file('file'), $request->safe()->except('file'), $request->user()->id);

        return response()->json(['media' => $media], 201);
    }

    public function update(UpdateMediaRequest $request, Media $media, ActivityLogger $activities): JsonResponse
    {
        $this->authorize('update', $media);
        $media->update($request->validated());
        $activities->record('media.updated', $media, $request->user()->id);

        return response()->json(['media' => $media->fresh()]);
    }

    public function destroy(Request $request, Media $media, MediaService $service): JsonResponse
    {
        $this->authorize('delete', $media);
        $service->delete($media, $request->user()->id);

        return response()->json(status: 204);
    }
}
