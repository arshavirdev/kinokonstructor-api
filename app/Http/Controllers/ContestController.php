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
        $query = Contest::query()->orderBy('id', 'desc');

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
    
        return new ContestResource($contest);
    }
}
