<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegionalBranch\BranchMemberRequest;
use App\Models\BranchMember;
use App\Models\RegionalBranch;
use Illuminate\Support\Facades\DB;

class BranchMemberController extends Controller
{
    public function store(RegionalBranch $regionalBranch, BranchMemberRequest $request)
    {
        DB::beginTransaction();

        try {
            foreach ($request->validated()['members'] as $memberData) {
                $image = $memberData['image_file'] ?? null;
                unset($memberData['image_file']);

                // Update if ID is present, otherwise create
                if (!empty($memberData['id'])) {
                    $member = $regionalBranch->members()->findOrFail($memberData['id']);
                    $member->update($memberData);
                } else {
                    $member = $regionalBranch->members()->create($memberData);
                }

                // If image is provided, replace existing media
                if ($image) {
                    $member->clearMediaCollection(BranchMember::IMAGE);
                    $member
                        ->addMedia($image)
                        ->toMediaCollection(BranchMember::IMAGE);
                }
            }

            DB::commit();

            return response()->json(['message' => 'Members stored or updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to process members',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
