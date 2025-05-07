<?php

namespace App\Service;

use App\Http\Requests\RegionalBranch\RegionalBranchRequest;
use App\Models\RegionalBranch;
use Illuminate\Http\UploadedFile;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;

class RegionalBranchService
{
    public function store(RegionalBranchRequest $request): ?RegionalBranch
    {
        $authUser = auth()->user();
        $insertData = $request->validated();
        $insertData['owner_id'] = $authUser->profile->id;

        $branch = RegionalBranch::create($insertData);

        $this->syncMediaCollection($branch, [
            'files' => $request->file(RegionalBranch::DOCS_FILES, []),
            'post' => $request->post(RegionalBranch::DOCS_FILES, []),
        ], RegionalBranch::DOCS_FILES);

        if ($request->has('contacts')) {
            $this->handleContacts($branch, $request->input('contacts')['project_contacts'], $authUser);
        }

        // Reload the relation to access media, news and member
        $branch->load(['members', 'news', 'media', 'contacts']);

        return $branch;
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function update(RegionalBranch $branch, RegionalBranchRequest $request): ?RegionalBranch
    {
        $authUser = auth()->user();
        $branch->update($request->validated());

        $this->syncMediaCollection($branch, [
            'files' => $request->file(RegionalBranch::DOCS_FILES, []),
            'post' => $request->post(RegionalBranch::DOCS_FILES, []),
        ], RegionalBranch::DOCS_FILES);

        if ($request->has('contacts')) {
            $this->handleContacts($branch, $request->input('contacts'), $authUser);
        }

        // Reload the relation to access media
        $branch->load(['members', 'news', 'media', 'contacts']);

        return $branch;
    }


    private function handleContacts(RegionalBranch $branch, $contacts, $user): void
    {
        $data = [
            'phone' => $contacts['phone'] ?? [],
            'email' => $contacts['email'] ?? [],
            'website' => $contacts['website'] ?? [],
            'socials' => $contacts['socials'] ?? [],
            'other' => $contacts['other'] ?? [],
        ];

        if ($branch->contacts) {
            $branch->contacts()->update($data);
            return;
        }

        $branch->contacts()->create(array_merge(['user_id' => $user->id], $data));
    }

    /**
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    private function syncMediaCollection(HasMedia $model, array $data, string $collectionName): void
    {
        $files = $data['files'] ?? [];
        $postData = $data['post'] ?? [];

        $mediaIdsToKeep = collect($postData)
            ->pluck('id')
            ->filter()
            ->toArray();

        //  Delete all media files exclude media IDs form req.body
        $model->getMedia($collectionName)
            ->reject(fn($media) => in_array($media->id, $mediaIdsToKeep))
            ->each->delete();

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $model->addMedia($file)->toMediaCollection($collectionName);
            }
        }
    }
}
