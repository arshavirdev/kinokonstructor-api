<?php

namespace App\Service;

use App\Http\Requests\StoreProjectRequestNEW;
use App\Http\Requests\UpdateProjectRequestNEW;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Request as ModelsRequest;
use Spatie\MediaLibrary\HasMedia;

class ProjectService
{
    public function favorite(Project $project)
    {
        $userId = Auth::id();
        $exists = $project->favorites()->where('user_id', $userId)->exists();

        $project->favorites()->where('user_id', $userId)->deleteIf($exists);

        if (!$exists) {
            $project->favorites()->create(['user_id' => $userId]);
        }

        return ['is_favorite' => !$exists];
    }

    public function archive(Project $project)
    {
        return $this->setArchiveStatus($project, true);
    }

    public function unarchive(Project $project)
    {
        return $this->setArchiveStatus($project, false);
    }

    public function store(StoreProjectRequestNEW $request): ?Project
    {
        $user = Auth::user();
        $projectData = $request->except(['files_section', 'id']);
        $projectData['owner_id'] = $user->profile->id;

        $project = Project::create($projectData);

        $this->handleRequests($project, [
            'files' => $request->file('requests', []),
            'post' => $request->post('requests', []),
        ], $user);

        if (isset($projectData['project_contacts'])) {
            $this->handleProjectContacts($project, $projectData['project_contacts'], $user);
        }

        if (isset($projectData['applicant_contacts'])) {
            $this->createOrUpdateApplicant($user, $project, $projectData);
        }

        $this->handleFileUploads($project, [
            'files' => $request->file('files_section', []),
            'post' => $request->post('files_section', []),
        ]);

        $this->syncMediaCollection($project, [
            'files' => $request->file('project_images', []),
            'post' => $request->post('project_images', []),
        ], Project::IMAGES);

        return $project;
    }

    public function update(UpdateProjectRequestNEW $request, Project $project): ?Project
    {
        $user = Auth::user();
        $projectData = $request->except(['files_section', 'id']);
        $projectData['owner_id'] = $user->profile->id;

        $project->update($projectData);

        $this->handleRequests($project, [
            'files' => $request->file('requests', []),
            'post' => $request->post('requests', []),
        ], $user);

        if (isset($projectData['project_contacts'])) {
            $this->handleProjectContacts($project, $projectData['project_contacts'], $user);
        }

        if (isset($projectData['applicant_contacts'])) {
            $this->createOrUpdateApplicant($user, $project, $projectData);
        }

        $this->handleFileUploads($project, [
            'files' => $request->file('files_section', []),
            'post' => $request->post('files_section', []),
        ]);

        $this->syncMediaCollection($project, [
            'files' => $request->file('project_images', []),
            'post' => $request->post('project_images', []),
        ], Project::IMAGES);


        return $project;
    }

    // ---------------------- Helper Methods ----------------------

    private function setArchiveStatus(Project $project, bool $status): array
    {
        $project->update(['is_archived' => $status]);
        return ['is_archived' => $status];
    }

    private function handleRequests(Project $project, array $data, $user): void
    {
        $postData = $data['post'];
        $project->requests()->delete();

        foreach (ModelsRequest::REQUEST_TYPES as $type) {
            if (isset($postData[$type])) {
                foreach ($postData[$type] as $index => $entry) {
                    $request = $project->requests()->create([
                        'user_id' => $user->id,
                        'name' => $entry['name'] ?? '',
                        'location' => $entry['location'] ?? '',
                        'season' => $entry['season'] ?? [],
                        'category' => $entry['category'] ?? [],
                        'info' => $entry['info'] ?? '',
                        'type' => $type,
                    ]);

                    $this->syncMediaCollection($request, [
                        'files' => $data['files'][$type][$index][ModelsRequest::DOCS_FILES] ?? [],
                        'post' => $entry[ModelsRequest::DOCS_FILES] ?? [],
                    ], ModelsRequest::DOCS_FILES);

                    $this->syncMediaCollection($request, [
                        'files' => $data['files'][$type][$index][ModelsRequest::IMAGES_FILES] ?? [],
                        'post' => $entry[ModelsRequest::IMAGES_FILES] ?? [],
                    ], ModelsRequest::IMAGES_FILES);
                }
            }
        }
    }

    private function handleProjectContacts(Project $project, array $contacts, Authenticatable $user): void
    {
        $privacy = [];

        if (($contacts['telVisible'] ?? true) === false) {
            $privacy[] = 'phone';
        }

        if (($contacts['emailVisible'] ?? true) === false) {
            $privacy[] = 'email';
        }

        $project->update(['privacy_hide' => $privacy]);

        $data = [
            'phone' => $contacts['phone'] ?? [],
            'email' => $contacts['email'] ?? [],
            'website' => $contacts['website'] ?? [],
            'socials' => $contacts['socials'] ?? [],
            'other' => $contacts['other'] ?? [],
        ];

        $contact = $project->contact;
        $contact ? $contact->update($data) : $project->contact()->create(array_merge(['user_id' => $user->id], $data));
    }

    private function handleFileUploads(Project $project, $data): void
    {
        foreach (Project::MEDIA_FILE_TYPES_MAPPING as $sectionKey => $mediaCollection) {
            $files = $data['files'][$sectionKey][0]['files'] ?? [];
            $postData = $data['post'][$sectionKey][0]['files'] ?? [];
            $this->syncMediaCollection($project, [
                'files' => $files,
                'post' => $postData,
            ], $mediaCollection);
        }
    }

    private function createOrUpdateApplicant(Authenticatable $user, Project $project, $projectData): void
    {
        $applicant = $project->applicant;
        $contacts = $projectData['applicant_contacts'];
        $nameParts = preg_split('/\s+/', trim($projectData['applicant_full_name']));
        $occupation_ids = $projectData['applicant_occupation_ids'];
        $privacy = [];

        if (($contacts['telVisible'] ?? true) === false) {
            $privacy[] = 'phone';
        }
        if (($contacts['emailVisible'] ?? true) === false) {
            $privacy[] = 'email';
        }

        $profileData = [
            'firstname' => $nameParts[0] ?? '',
            'lastname' => $nameParts[1] ?? '',
            'middlename' => $nameParts[2] ?? '',
            'privacy_hide' => $privacy,
        ];

        $contactData = [
            'user_id' => $user->id,
            'phone' => $contacts['phone'] ?? [],
            'email' => $contacts['email'] ?? [],
            'website' => $contacts['website'] ?? [],
            'socials' => $contacts['socials'] ?? [],
            'other' => $contacts['other'] ?? [],
        ];

        if ($applicant) {
            $applicant->update($profileData);
            $applicant->occupations()->sync($occupation_ids);

            if (!$applicant->contact) {
                $applicant->contact()->create($contactData);
            } else {
                $applicant->contact->update($contactData);
            }
        } else {
            $newApplicant = Profile::create(array_merge($profileData, ['gender' => 'm']));
            $newApplicant->occupations()->sync($occupation_ids);
            $project->update(['applicant_id' => $newApplicant->id]);
            $newApplicant->contact()->create($contactData);
        }
    }

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
