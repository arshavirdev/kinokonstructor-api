<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContestRequest;
use App\Http\Requests\UpdateContestRequest;
use App\Http\Resources\ContestResource;
use Illuminate\Http\Request;
use App\Models\Contest;
use App\Models\ContestContact;
use App\Service\ContestService;
use Illuminate\Support\Facades\Auth;

class ContestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Contest::query()
            ->with('owner')
            ->orderBy('id', 'desc')
            ->withCount([
                'favorites as is_favorite' => function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                }
            ]);

        if ($request->has('type') && $request->input('type') === 'my') {
            $profileId = $user->profile?->id;
            if (!$profileId) abort(421);
            $query = $query->where('owner_id', $profileId);
        }

        if ($request->has('title'))
            $query = $query->where('title', 'ILIKE', '%' . $request->input('title') . '%');

        $contests = $query->paginate();
        return ContestResource::collection($contests);
    }

    public function show($id)
    {
        $contest = Contest::with(['contacts', 'media'])->findOrFail($id);
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

        // Handle gallery images
        if ($request->hasFile('gallery')) {
            foreach ($request->file('gallery') as $photo) {
                $contest->addMedia($photo)->toMediaCollection(Contest::GALLERY);
            }
        }

        // Handle document uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $document) {
                $contest->addMedia($document)->toMediaCollection(Contest::DOCUMENTS);
            }
        }

        // Handle logo upload (single file)
        if ($request->hasFile('logo')) {
            $contest->addMedia($request->file('logo'))->toMediaCollection(Contest::LOGO);
        }

        // Handle photo gallery images
        if ($request->hasFile('photo_gallery')) {
            foreach ($request->file('photo_gallery') as $photo) {
                $contest->addMedia($photo)->toMediaCollection(Contest::PHOTO_GALLERY);
            }
        }

        // Handle partner images
        if ($request->hasFile('partners')) {
            foreach ($request->file('partners') as $partner) {
                $contest->addMedia($partner)->toMediaCollection(Contest::PARTNERS);
            }
        }
    
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

        // Handle gallery images
        if ($request->hasFile('gallery')) {
            $contest->clearMediaCollection(Contest::GALLERY);
            foreach ($request->file('gallery') as $photo) {
                $contest->addMedia($photo)->toMediaCollection(Contest::GALLERY);
            }
        }

        // Handle document uploads
        if ($request->hasFile('documents')) {
            $contest->clearMediaCollection(Contest::DOCUMENTS);
            foreach ($request->file('documents') as $document) {
                $contest->addMedia($document)->toMediaCollection(Contest::DOCUMENTS);
            }
        }

        // Handle logo upload (Replace old logo)
        if ($request->hasFile('logo')) {
            $contest->clearMediaCollection(Contest::LOGO);
            $contest->addMedia($request->file('logo'))->toMediaCollection(Contest::LOGO);
        }

        // Handle photo gallery images
        if ($request->hasFile('photo_gallery')) {
            $contest->clearMediaCollection(Contest::PHOTO_GALLERY);
            foreach ($request->file('photo_gallery') as $photo) {
                $contest->addMedia($photo)->toMediaCollection(Contest::PHOTO_GALLERY);
            }
        }

        // Handle partner images
        if ($request->hasFile('partners')) {
            $contest->clearMediaCollection(Contest::PARTNERS);
            foreach ($request->file('partners') as $partner) {
                $contest->addMedia($partner)->toMediaCollection(Contest::PARTNERS);
            }
        }

        return new ContestResource($contest);
    }

    public function destroy(Contest $contest)
    {
        $contest->delete();
    }

    public function action(Contest $contest, string $action, ContestService $contestService)
    {
        // Allowed actions
        $allowedActions = ['favorite', 'archive', 'unarchive'];

        if (!in_array($action, $allowedActions)) {
            return response()->json(['message' => 'Invalid action'], 400);
        }

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
