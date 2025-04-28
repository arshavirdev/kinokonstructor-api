<?php

namespace App\Service;

use App\Http\Requests\StoreProjectRequestNEW;
use App\Http\Requests\UpdateProjectRequestNEW;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
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

        if ($exists) {
            $project->favorites()->where('user_id', $userId)->delete();
            return ['is_favorite' => false];
        }

        $project->favorites()->create(['user_id' => $userId]);
        return ['is_favorite' => true];
    }

    public function archive(Project $project)
    {
        $project->update(['is_archived' => true]);
        return ['is_archived' => true];
    }

    public function unarchive(Project $project)
    {
        $project->update(['is_archived' => false]);
        return ['is_archived' => false];
    }

    public function store(StoreProjectRequestNEW $request): Project|null
    {
        $user = Auth::user();
        $projectData = $request->except(['files_section']);
        $projectData['owner_id'] = $user->profile->id;

        $project = Project::create($projectData);

        // Handle requests section
        if (isset($projectData['requests'])) {
            $data = $projectData['requests'];

            foreach (ModelsRequest::REQUEST_TYPES as $type) {
                if (!empty($data[$type]) && is_array($data[$type])) {
                    foreach ($data[$type] as $entry) {
                        $request = $project->requests()->create([
                            'user_id' => $user->id,
                            'name' => $entry['name'] ?? '',
                            'location' => $entry['location'] ?? '',
                            'season' => $entry['season'] ?? '',
                            'info' => $entry['info'] ?? '',
                            'type' => $type,
                        ]);

                        if (isset($entry['docs_files']) && is_array($entry['docs_files'])) {
                            foreach ($entry['docs_files'] as $file) {
                                $request->addMedia($file)->toMediaCollection(ModelsRequest::DOCS_FILES);
                            }
                        }
                    }
                }
            }
        }

        // Handle project contacts creation
        if (isset($projectData['project_contacts'])) {
            $contacts = $projectData['project_contacts'];

            $project_privacy_hide = [];

            if ($projectData['project_contacts']['telVisible'] === false) {
                $project_privacy_hide[] = 'phone';
            }

            if ($projectData['project_contacts']['emailVisible'] === false) {
                $project_privacy_hide[] = 'email';
            }

            $project->privacy_hide = $project_privacy_hide;
            $project->save();

            $project->contact()->create([
                'user_id' => $user->id,
                'phone' => $contacts['phone'] ?? [],
                'email' => $contacts['email'] ?? [],
                'website' => $contacts['website'] ?? [],
                'socials' => $contacts['socials'] ?? [],
                'other' => $contacts['other'] ?? [],
            ]);
        }

        // Handle applicant(profile) and applicant contacts creation
        if (isset($projectData['applicant_contacts'])) {
            $this->createOrUpdateApplicant($user, $project, $projectData);
        }

        // Handle file uploads from files_section
        if ($request->has('files_section')) {

            foreach (Project::MEDIA_FILE_TYPES_MAPPING as $sectionKey => $mediaCollection) {
                $sectionGroups = $request->file("files_section.$sectionKey", []);

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

        if ($request->has('project_images')) {
            $projectImages = $request->file('project_images');
            foreach ($projectImages as $image) {
                $file = $image;
                if ($file && $file->isValid()) {
                    $project->addMedia($file)->toMediaCollection(Project::IMAGES);
                }
            }
        }

        return $project;
    }

    public function update(UpdateProjectRequestNEW $request, Project $project): Project|null
    {
        $user = Auth::user();
        $projectData = $request->except(['files_section']);
        $projectData['owner_id'] = $user->profile->id;

        // Update basic project fields
        $project->update($projectData);

        // Handle requests section
        if (isset($projectData['requests'])) {
            // Delete old requests
            $project->requests()->delete();

            $data = $projectData['requests'];

            foreach (ModelsRequest::REQUEST_TYPES as $type) {
                if (!empty($data[$type]) && is_array($data[$type])) {
                    foreach ($data[$type] as $entry) {
                        $projectRequest = $project->requests()->create([
                            'user_id' => $user->id,
                            'name' => $entry['name'] ?? '',
                            'location' => $entry['location'] ?? '',
                            'season' => $entry['season'] ?? '',
                            'info' => $entry['info'] ?? '',
                            'type' => $type,
                        ]);

                        $projectRequest->clearMediaCollection(ModelsRequest::DOCS_FILES);
                        if (isset($entry['docs_files']) && is_array($entry['docs_files'])) {
                            foreach ($entry['docs_files'] as $file) {
                                $projectRequest->addMedia($file)->toMediaCollection(ModelsRequest::DOCS_FILES);
                            }
                        }
                    }
                }
            }
        }

        // Handle project contacts
        if (isset($projectData['project_contacts'])) {
            $contacts = $projectData['project_contacts'];

            $project_privacy_hide = [];

            if ($contacts['telVisible'] === false) {
                $project_privacy_hide[] = 'phone';
            }

            if ($contacts['emailVisible'] === false) {
                $project_privacy_hide[] = 'email';
            }

            $project->privacy_hide = $project_privacy_hide;
            $project->save();

            // Update or create contact
            $contact = $project->contact;
            if ($contact) {
                $contact->update([
                    'phone' => $contacts['phone'] ?? [],
                    'email' => $contacts['email'] ?? [],
                    'website' => $contacts['website'] ?? [],
                    'socials' => $contacts['socials'] ?? [],
                    'other' => $contacts['other'] ?? [],
                ]);
            } else {
                $project->contact()->create([
                    'user_id' => $user->id,
                    'phone' => $contacts['phone'] ?? [],
                    'email' => $contacts['email'] ?? [],
                    'website' => $contacts['website'] ?? [],
                    'socials' => $contacts['socials'] ?? [],
                    'other' => $contacts['other'] ?? [],
                ]);
            }
        }

        // Handle applicant(profile) and applicant contacts
        if (isset($projectData['applicant_contacts'])) {
            $this->createOrUpdateApplicant($user, $project, $projectData);
        }

        // Handle file uploads (replace old media first)
        if ($request->has('files_section')) {
            foreach (Project::MEDIA_FILE_TYPES_MAPPING as $sectionKey => $mediaCollection) {
                // First, clear the media collection
                $project->clearMediaCollection($mediaCollection);

                $sectionGroups = $request->file("files_section.$sectionKey", []);

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

        // Handle project images upload (replace old images first)
        if ($request->has('project_images')) {
            // Clear the project images collection first
            $project->clearMediaCollection(Project::IMAGES);

            $projectImages = $request->file('project_images');
            foreach ($projectImages as $image) {
                if ($image && $image->isValid()) {
                    $project->addMedia($image)->toMediaCollection(Project::IMAGES);
                }
            }
        }

        return $project;
    }

    /**
     * Create or Update Applicant and applicant contacts
     * @param User $user
     * @param Project $project
     * @param $projectData
     * @return void
     */
    private function createOrUpdateApplicant(User $user, Project $project, $projectData): void
    {
        $applicant = $project->applicant;

        $nameParts = preg_split('/\s+/', trim($projectData['applicant_full_name']));
        $occupation_ids = $projectData['applicant_occupation_ids'];

        $profile_privacy_hide = [];

        if ($projectData['applicant_contacts']['telVisible'] === false) {
            $profile_privacy_hide[] = 'phone';
        }

        if ($projectData['applicant_contacts']['emailVisible'] === false) {
            $profile_privacy_hide[] = 'email';
        }

        if ($applicant) {
            $applicant->update([
                'firstname' => $nameParts[0] ?? '',
                'lastname' => $nameParts[1] ?? '',
                'middlename' => $nameParts[2] ?? '',
                'privacy_hide' => $profile_privacy_hide,
            ]);

            $applicant->occupations()->sync($occupation_ids);

            $applicantContact = $applicant->contact;
            if ($applicantContact) {
                $applicantContact->update([
                    'phone' => $projectData['applicant_contacts']['phone'] ?? [],
                    'email' => $projectData['applicant_contacts']['email'] ?? [],
                    'website' => $projectData['applicant_contacts']['website'] ?? [],
                    'socials' => $projectData['applicant_contacts']['socials'] ?? [],
                    'other' => $projectData['applicant_contacts']['other'] ?? [],
                ]);
            } else {
                $applicant->contact()->create([
                    'user_id' => $user->id,
                    'phone' => $projectData['applicant_contacts']['phone'] ?? [],
                    'email' => $projectData['applicant_contacts']['email'] ?? [],
                    'website' => $projectData['applicant_contacts']['website'] ?? [],
                    'socials' => $projectData['applicant_contacts']['socials'] ?? [],
                    'other' => $projectData['applicant_contacts']['other'] ?? [],
                ]);
            }
        } else {
            // If applicant not exists, create new
            $newApplicant = (new Profile())->create([
                'firstname' => $nameParts[0] ?? '',
                'lastname' => $nameParts[1] ?? '',
                'middlename' => $nameParts[2] ?? '',
                'gender' => 'm', // TODO: check
                'privacy_hide' => $profile_privacy_hide,
            ]);

            $newApplicant->occupations()->sync($occupation_ids);

            $project->applicant_id = $newApplicant->id;
            $project->save();

            $newApplicant->contact()->create([
                'user_id' => $user->id,
                'phone' => $projectData['applicant_contacts']['phone'] ?? [],
                'email' => $projectData['applicant_contacts']['email'] ?? [],
                'website' => $projectData['applicant_contacts']['website'] ?? [],
                'socials' => $projectData['applicant_contacts']['socials'] ?? [],
                'other' => $projectData['applicant_contacts']['other'] ?? [],
            ]);
        }
    }

}
