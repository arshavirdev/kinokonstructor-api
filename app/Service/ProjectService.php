<?php

namespace App\Service;

use App\Http\Requests\StoreProjectRequestNEW;
use App\Http\Requests\UpdateProjectRequestNEW;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Profile;
use App\Models\Project;
use App\Models\Request as ModelsRequest;

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

        if (isset($projectData['requests'])) {
            $this->handleRequests($project, $projectData['requests'], $user);
        }

        if (isset($projectData['project_contacts'])) {
            $this->handleProjectContacts($project, $projectData['project_contacts'], $user);
        }

        if (isset($projectData['applicant_contacts'])) {
            $this->createOrUpdateApplicant($user, $project, $projectData);
        }

        if (!empty($request->file('files_section'))) {
            $this->handleFileUploads($project, $request->file('files_section'), true);
        }

        if (!empty($request->file('project_images'))) {
            $this->handleImagesUpload($project, $request->file('project_images'), true);
        }

        return $project;
    }

    public function update(UpdateProjectRequestNEW $request, Project $project): ?Project
    {
        $user = Auth::user();
        $projectData = $request->except(['files_section', 'id']);
        $projectData['owner_id'] = $user->profile->id;

        $project->update($projectData);

        if (isset($projectData['requests'])) {
            $this->handleRequests($project, $projectData['requests'], $user);
        }

        if (isset($projectData['project_contacts'])) {
            $this->handleProjectContacts($project, $projectData['project_contacts'], $user);
        }

        if (isset($projectData['applicant_contacts'])) {
            $this->createOrUpdateApplicant($user, $project, $projectData);
        }

        if (!empty($request->file('files_section'))) {
            $this->handleFileUploads($project, $request->file('files_section'), true);
        }

        if (!empty($request->file('project_images'))) {
            $this->handleImagesUpload($project, $request->file('project_images'), true);
        }

        return $project;
    }

    // ---------------------- Helper Methods ----------------------

    private function setArchiveStatus(Project $project, bool $status): array
    {
        $project->update(['is_archived' => $status]);
        return ['is_archived' => $status];
    }

    private function handleRequests(Project $project, array $data, User $user): void
    {
        $project->requests()->delete();

        foreach (ModelsRequest::REQUEST_TYPES as $type) {
            if (!empty($data[$type]) && is_array($data[$type])) {
                foreach ($data[$type] as $entry) {
                    $request = $project->requests()->create([
                        'user_id' => $user->id,
                        'name' => $entry['name'] ?? '',
                        'location' => $entry['location'] ?? '',
                        'season' => $entry['season'] ?? [],
                        'category' => $entry['category'] ?? [],
                        'info' => $entry['info'] ?? '',
                        'type' => $type,
                    ]);

                    $this->attachMedia($request, $entry, ModelsRequest::DOCS_FILES);
                    $this->attachMedia($request, $entry, ModelsRequest::IMAGES_FILES);
                }
            }
        }
    }

    private function attachMedia($model, array $entry, string $collection): void
    {
        if (!isset($entry[$collection]) || !is_array($entry[$collection])) {
            return;
        }

        if (method_exists($model, 'clearMediaCollection')) {
            $model->clearMediaCollection($collection);
        }

        foreach ($entry[$collection] as $file) {
            if ($file && $file->isValid()) {
                $model->addMedia($file)->toMediaCollection($collection);
            }
        }
    }

    private function handleProjectContacts(Project $project, array $contacts, User $user): void
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

    private function handleFileUploads(Project $project, array $filesSection, bool $replace = false): void
    {
        foreach (Project::MEDIA_FILE_TYPES_MAPPING as $sectionKey => $mediaCollection) {
            if ($replace) {
                $project->clearMediaCollection($mediaCollection);
            }

            $sectionGroups = $filesSection[$sectionKey] ?? [];

            foreach ($sectionGroups as $group) {
                $files = $group['files'] ?? [];

                foreach ($files as $file) {
                    if ($file && $file->isValid()) {
                        $project->addMedia($file)->toMediaCollection($mediaCollection);
                    }
                }
            }
        }
    }

    private function handleImagesUpload(Project $project, array $images, bool $replace = false): void
    {
        if ($replace) {
            $project->clearMediaCollection(Project::IMAGES);
        }

        foreach ($images as $file) {
            if ($file && $file->isValid()) {
                $project->addMedia($file)->toMediaCollection(Project::IMAGES);
            }
        }
    }

    private function createOrUpdateApplicant(User $user, Project $project, $projectData): void
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
            $applicant->contact->update($contactData);
        } else {
            $newApplicant = Profile::create(array_merge($profileData, ['gender' => 'm']));
            $newApplicant->occupations()->sync($occupation_ids);
            $project->update(['applicant_id' => $newApplicant->id]);
            $newApplicant->contact()->create($contactData);
        }
    }
}
