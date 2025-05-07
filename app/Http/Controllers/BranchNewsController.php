<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegionalBranch\BranchNewsRequest;
use App\Models\BranchNews;
use App\Models\RegionalBranch;
use Illuminate\Support\Facades\DB;

class BranchNewsController extends Controller
{
    public function store(RegionalBranch $regionalBranch, BranchNewsRequest $request)
    {
        DB::beginTransaction();

        try {
            foreach ($request->validated()['news'] as $newsData) {
                $image = $newsData['image_file'] ?? null;
                unset($newsData['image_file']);

                // Update if ID is present, otherwise create
                if (!empty($newsData['id'])) {
                    $member = $regionalBranch->news()->findOrFail($newsData['id']);
                    $member->update($newsData);
                } else {
                    $member = $regionalBranch->news()->create($newsData);
                }

                // If image is provided, replace existing media
                if ($image) {
                    $member->clearMediaCollection(BranchNews::IMAGE);
                    $member
                        ->addMedia($image)
                        ->toMediaCollection(BranchNews::IMAGE);
                }
            }

            DB::commit();

            return response()->json(['message' => 'News stored or updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to process news',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
