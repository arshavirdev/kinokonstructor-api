<?php

namespace App\Http\Controllers;

use App\DTOs\MediaSyncDataDTO;
use App\Http\Requests\StoreContestApplicationRequest;
use App\Http\Requests\StoreContestRequest;
use App\Http\Requests\UpdateContestRequest;
use App\Http\Resources\ContestResource;
use App\Service\Media\MediaService;
use Illuminate\Http\Request;
use App\Models\Contest;
use App\Models\ContestContact;
use App\Service\ContestService;
use Illuminate\Support\Facades\Auth;

class ContestController extends Controller
{

    public function __construct(
        private ContestService $contestService,
        private MediaService $mediaService
    ) {

    }
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Contest::query()
            ->with('owner')
            ->visibleTo($user)
            ->orderBy('id', 'desc')
            ->withCount([
                'favorites as is_favorite' => function ($query) use ($user) {
                    if ($user) {
                        $query->where('user_id', $user->id);
                    }
                }
            ]);

        if ($request->has('type') && $request->input('type') === 'my') {
            $profileId = $user->profile?->id;
            if (!$profileId)
                abort(421);
            $query = $query->where('owner_id', $profileId);
        }

        if ($request->has('favorite') && $request->input('favorite') === 'true') {
            $query->whereHas('favorites', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        if ($request->has('filter.location')) {
            $regionId = $request->input('filter.location');
            $query = $query->where('region', $regionId);
        }

        if ($request->has('title'))
            $query = $query->where('title', 'ILIKE', '%' . $request->input('title') . '%');

        $contests = $query->paginate();
        return ContestResource::collection($contests);
    }

    public function show(Contest $contest)
    {
        $user = auth()->user();
        $contest = Contest::visibleTo($user)
            ->whereKey($contest->getKey())
            ->with(['contacts', 'media'])
            ->firstOrFail();

        $contest = $contest->load(['contacts', 'media']);
        return new ContestResource($contest);
    }

    public function store(StoreContestRequest $request)
    {
        $params = $request->except(['gallery', 'documents', 'logo', 'photo_gallery', 'partners', 'contacts']);
        $profile = Auth::user()->profile;
        $params['owner_id'] = $profile['id'];
        $contest = Contest::create($params);

        // Handle contacts
        if ($request->has('contacts')) {
            $contactsData = $request->input('contacts');
            $contactsData['contest_id'] = $contest->id;
            ContestContact::create($contactsData);
        }

        $galleryMediaDto = new MediaSyncDataDTO(
            $request->file('gallery', []),
            $request->post('gallery', [])
        );
        $this->mediaService->syncMediaCollection($contest, $galleryMediaDto, Contest::GALLERY);

        $documentsMediaDto = new MediaSyncDataDTO(
            $request->file('documents', []),
            $request->post('documents', [])
        );
        $this->mediaService->syncMediaCollection($contest, $documentsMediaDto, Contest::DOCUMENTS);

        $logoMediaDto = new MediaSyncDataDTO(
            $request->file('logo', []),
            $request->post('logo', [])
        );
        $this->mediaService->syncMediaCollection($contest, $logoMediaDto, Contest::LOGO);

        $photoGalleryMediaDto = new MediaSyncDataDTO(
            $request->file('photo_gallery', []),
            $request->post('photo_gallery', [])
        );
        $this->mediaService->syncMediaCollection($contest, $photoGalleryMediaDto, Contest::PHOTO_GALLERY);

        $partnerMediaDto = new MediaSyncDataDTO(
            $request->file('partners', []),
            $request->post('partners', [])
        );
        $this->mediaService->syncMediaCollection($contest, $partnerMediaDto, Contest::PARTNERS);

        return new ContestResource($contest);
    }

    public function update(UpdateContestRequest $request, Contest $contest)
    {
        $params = $request->except(['gallery', 'documents', 'logo', 'photo_gallery', 'partners', 'contacts']);

        // Update contest fields
        $contest->update($params);

        // Handle contacts
        if ($request->has('contacts')) {
            $contactsData = $request->input('contacts');
            ContestContact::updateOrCreate(
                ['contest_id' => $contest->id],
                array_merge($contactsData, ['contest_id' => $contest->id])
            );
        }

        $galleryMediaDto = new MediaSyncDataDTO(
            $request->file('gallery', []),
            $request->post('gallery', [])
        );
        $this->mediaService->syncMediaCollection($contest, $galleryMediaDto, Contest::GALLERY);

        $documentsMediaDto = new MediaSyncDataDTO(
            $request->file('documents', []),
            $request->post('documents', [])
        );
        $this->mediaService->syncMediaCollection($contest, $documentsMediaDto, Contest::DOCUMENTS);

        $logoMediaDto = new MediaSyncDataDTO(
            $request->file('logo', []),
            $request->post('logo', [])
        );
        $this->mediaService->syncMediaCollection($contest, $logoMediaDto, Contest::LOGO);

        $photoGalleryMediaDto = new MediaSyncDataDTO(
            $request->file('photo_gallery', []),
            $request->post('photo_gallery', [])
        );
        $this->mediaService->syncMediaCollection($contest, $photoGalleryMediaDto, Contest::PHOTO_GALLERY);

        $partnerMediaDto = new MediaSyncDataDTO(
            $request->file('partners', []),
            $request->post('partners', [])
        );
        $this->mediaService->syncMediaCollection($contest, $partnerMediaDto, Contest::PARTNERS);

        return new ContestResource($contest);
    }

    public function destroy(Contest $contest)
    {
        $contest->delete();
    }

    public function action(Contest $contest, string $action, ContestService $contestService)
    {
        if (in_array($action, ['archive', 'unarchive'])) {
            $this->authorize($action, $contest);
        }

        $result = match ($action) {
            'favorite' => $contestService->favorite($contest),
            'archive' => $contestService->archive($contest),
            'unarchive' => $contestService->unarchive($contest),
            default => response()->json(['message' => 'Invalid action'], 400)
        };

        if (isset($result['error'])) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

        return response()->json($result);
    }
}
