<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContestRequest;
use App\Http\Resources\ContestResource;
use Illuminate\Http\Request;
use App\Models\Contest;
use App\Models\ContestContact;

class ContestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $query = Contest::query();

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
        \Log::info(request()->all());
        $params = $request->except(['gallery', 'documents', 'logo', 'photo_gallery', 'partners', 'contacts']);
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


        // $params = $request->validated();

        // Create or update

        // $contest = Contest::create([
        //     'title' => $request->input('title'),
        //     'type' => $request->input('type'),
        //     'years_held' => $request->input('years_held'),
        //     'country' => $request->input('country'),
        //     'region' => $request->input('region'),
        //     'city' => $request->input('city'),
        //     'description' => $request->input('description'),
        //     // 'gallery' => json_encode($request->input('gallery', [])),
        //     'conditions' => $request->input('conditions'),
        //     'deadline_title' => $request->input('deadline_title'),
        //     'deadline_date' => $request->input('deadline_date'),
        //     'prizes' => $request->input('prizes'),
        //     'adjudicator' => $request->input('adjudicator'),
        //     'organizers' => $request->input('organizers'),
        //     // 'documents' => json_encode($request->input('general_info.documents', [])),

        //     'online_application' => $request->input('online_application'),
        //     // 'logo' => $request->file('visual_info.logo') ? $request->file('logo')->store('logos') : null,
        //     'video' => $request->input('video'),
        //     // 'photo_gallery' => json_encode($request->input('photo_gallery', [])),
        //     // 'partners' => json_encode($request->input('partners', [])),
        // ]);
    
        // 1. TODO: Create or update logic
        // 2. Move gallery,documents to media tbl
        // 3. Add vk-link
        // 4. Remove visual table (done)
        // 5. Imlement addMedia, ... add constatns, hasMedia ...
        // $profile->addMedia($params['avatar'])->toMediaCollection(Profile::AVATAR_MEDIA);

        // if ($request->has('contacts.contest_id')) {
        //     Contest::update([

        //     ]);
        // }
    
        // use media, 
        // ContestContact::create([
        //     'contest_id' => $contest->id,
        //     'website' => $request->input('contacts.website'),
        //     'social_media' => $request->input('contacts.social_media'),
        //     'email' => $request->input('contacts.email'),
        //     'phone' => $request->input('contacts.phone'),
        //     'postal_address' => $request->input('contacts.postal_address'),
        //     'button_name' => $request->input('contacts.button_name')
        // ]);

        // $this->syncMedia($contest, $params);
    
        return new ContestResource($contest);
    }

    private function syncMedia(Contest $contest, $params)
    {
        $mediaCollections = [
            'gallery' => Contest::GALLERY,
            'documents' => Contest::DOCUMENTS,
            'logo' => Contest::LOGO,
            'photo_gallery' => Contest::PHOTO_GALLERY,
            'partners' => Contest::PARTNERS,
        ];

        foreach ($mediaCollections as $paramKey => $collectionName) {
            if (array_key_exists($paramKey, $params)) {
                $requestMedia = collect($params[$paramKey]);
                $toSaveMedia = $requestMedia->filter(fn($item) => is_object($item)); // New files
                $toKeepMediaIds = $requestMedia->filter(fn($item) => !is_object($item))->map(fn($id) => ['id' => (int)$id]); // Existing files

                $contest->clearMediaCollectionExcept($collectionName, $toKeepMediaIds);

                foreach ($toSaveMedia as $media) {
                    $contest->addMedia($media)->toMediaCollection($collectionName);
                }
            }
        }
    }
}
