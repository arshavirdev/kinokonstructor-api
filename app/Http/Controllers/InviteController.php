<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;

use App\Mail\ProjectInvitation;
use App\Models\Profile;
use App\Models\ProjectMember;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Models\Project;

class InviteController extends Controller
{

    public function inviteMember(Project $project, Request $request)
    {
        $request->validate([
            'profileId' => 'required|exists:profiles,id',
            'role' => 'required|string',
            'type' => 'required|string'
        ]);

        $profile = Profile::find($request->integer('profileId'));

        $member = new ProjectMember([
            'project_id' => $project->id,
            'profile_id' => $profile->id,
            'role' => (string)$request->string('role'),
            'type' => (string)$request->string('type'),
            'invitation_code' => \Str::random(32),
            'data' => $request->input('data', [])
        ]);
        $previousCount = ProjectMember::query()
            ->where('project_id', $member->project_id)
            ->where('profile_id', $member->profile_id)
            ->where('type', $member->type)
            ->whereIn('status', ['pending', 'approved'])->count('id');
        if ($previousCount > 0) {
            abort(422, 'already_invited');
        }
        $member->save();
        $invitedUser = $profile->user;

        try {
            \Mail::to($invitedUser)->send(new ProjectInvitation($invitedUser, $member, $project));
        } catch (\Exception $exception) {

        }
        return $invitedUser;
    }

    public function removeMember(Project $project, ProjectMember $projectMember)
    {
        if (!$projectMember) return;
        if ($projectMember->project_id !== $project->id) return;
        $projectMember->delete();
        return true;
    }

    public function acceptInvite(Request $request)
    {
        $invite = ProjectMember::where('id', $request->input('id'))->where('invitation_code', $request->input('token'))->first();
        if (!$invite) abort(401);
        $invite->status = 'accepted';
        $invite->save();
        $url = env('SPA_URL') . '/projects/';
        return redirect($url);
    }

    public function rejectInvite(Request $request)
    {
        $invite = ProjectMember::where('id', $request->input('id'))->where('invitation_code', $request->input('token'))->first();
        if (!$invite) abort(401);
        $invite->status = 'rejected';
        $invite->save();
        $url = env('SPA_URL') . '/projects/';
        return redirect($url);
    }
}
