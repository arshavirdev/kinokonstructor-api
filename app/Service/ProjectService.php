<?php

namespace App\Service;

use App\DTOs\MediaSyncDataDTO;
use App\Http\Requests\StoreProjectRequestNEW;
use App\Http\Requests\UpdateProjectRequestNEW;
use App\Service\Media\MediaService;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use App\Models\Request as ModelsRequest;
use App\Models\Profile;
use App\Models\Project;

class ProjectService
{
    private MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function favorite(Project $project)
    {
        $userId = Auth::id();
        $exists = $project->favorites()->where('user_id', $userId)->exists();

        if ($exists) {
            $project->favorites()->where('user_id', $userId)->delete();
            return ['is_favorite' => false];
        }

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

        $requestsMediaDto = new MediaSyncDataDTO(
            $request->file('requests', []),
            $request->post('requests', [])
        );

        $imagesMediaDto = new MediaSyncDataDTO(
            $request->file('project_images', []),
            $request->post('project_images', [])
        );

        $filesMediaDto = new MediaSyncDataDTO(
            $request->file('files_section', []),
            $request->post('files_section', [])
        );

        $this->handleRequests($project, $requestsMediaDto, $user);

        if (isset($projectData['project_contacts'])) {
            $this->handleProjectContacts($project, $projectData['project_contacts'], $user);
        }

        if (isset($projectData['applicant_contacts'])) {
            $this->createOrUpdateApplicant($user, $project, $projectData);
        }

        $this->handleFileUploads($project, $filesMediaDto);

        $this->mediaService->syncMediaCollection($project, $imagesMediaDto, Project::IMAGES);

        return $project;
    }

    public function update(UpdateProjectRequestNEW $request, Project $project): ?Project
    {
        $user = Auth::user();
        $projectData = $request->except(['files_section', 'id']);
        $projectData['owner_id'] = $user->profile->id;

        $project->update($projectData);

        $requestsMediaDto = new MediaSyncDataDTO(
            $request->file('requests', []),
            $request->post('requests', [])
        );

        $imagesMediaDto = new MediaSyncDataDTO(
            $request->file('project_images', []),
            $request->post('project_images', [])
        );

        $filesMediaDto = new MediaSyncDataDTO(
            $request->file('files_section', []),
            $request->post('files_section', [])
        );

        $this->handleRequests($project, $requestsMediaDto, $user);

        if (isset($projectData['project_contacts'])) {
            $this->handleProjectContacts($project, $projectData['project_contacts'], $user);
        }

        if (isset($projectData['applicant_contacts'])) {
            $this->createOrUpdateApplicant($user, $project, $projectData);
        }

        $this->handleFileUploads($project, $filesMediaDto);

        $this->mediaService->syncMediaCollection($project, $imagesMediaDto, Project::IMAGES);

        return $project;
    }

    // ---------------------- Helper Methods ----------------------

    private function setArchiveStatus(Project $project, bool $status): array
    {
        $project->update(['is_archived' => $status]);
        return ['is_archived' => $status];
    }

    private function handleRequests(Project $project, MediaSyncDataDTO $mediaDto, $user): void
    {
        foreach (ModelsRequest::REQUEST_TYPES as $requestType) {
            if (isset($mediaDto->post[$requestType])) {
                $requests = $mediaDto->post[$requestType];
                // Keep passed post requests and delete rest...
                $keepRequestIds = collect($requests)->pluck('id')->toArray();
                $project->requests()
                    ->whereNotIn('id', $keepRequestIds)
                    ->where('type', $requestType)
                    ->delete();

                foreach ($requests as $index => $entry) {
                    $request = $project->requests()->updateOrCreate(
                        ['id' => $entry['id'] ?? null],
                        [
                            'user_id' => $user->id,
                            'name' => $entry['name'] ?? '',
                            'location' => $entry['location'] ?? '',
                            'season' => $entry['season'] ?? [],
                            'category' => $entry['category'] ?? [],
                            'info' => $entry['info'] ?? '',
                            'type' => $requestType,
                        ]
                    );

                    $mediaFilesDto = new MediaSyncDataDTO(
                        $mediaDto->files[$requestType][$index][ModelsRequest::DOCS_FILES] ?? [],
                        $entry[ModelsRequest::DOCS_FILES] ?? [],
                    );

                    $this->mediaService->syncMediaCollection($request, $mediaFilesDto, ModelsRequest::DOCS_FILES);

                    $mediaImagesDto = new MediaSyncDataDTO(
                        $mediaDto->files[$requestType][$index][ModelsRequest::IMAGES_FILES] ?? [],
                        $entry[ModelsRequest::IMAGES_FILES] ?? [],
                    );
                    $this->mediaService->syncMediaCollection($request, $mediaImagesDto, ModelsRequest::IMAGES_FILES);
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
        $contact ? $contact->update($data) : $project->contacts()->create(array_merge(['user_id' => $user->id], $data));
    }

    private function handleFileUploads(Project $project, MediaSyncDataDTO $mediaDto): void
    {
        foreach (Project::MEDIA_FILE_TYPES_MAPPING as $sectionKey => $mediaCollection) {
            $sectionMediaDto = new MediaSyncDataDTO(
                $mediaDto->files[$sectionKey][0]['files'] ?? [],
                $mediaDto->post[$sectionKey][0]['files'] ?? []
            );
            $this->mediaService->syncMediaCollection($project, $sectionMediaDto, $mediaCollection);
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
}
