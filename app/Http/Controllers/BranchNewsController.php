<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegionalBranch\BranchNewsRequest;
use App\Http\Resources\RegionalBranch\BranchNewsResource;
use App\Models\BranchNews;
use App\Models\RegionalBranch;
use Illuminate\Support\Facades\DB;

class BranchNewsController extends Controller
{
    public function show(int $id)
    {
        $branchNews = BranchNews::findOrFail($id);
        return new BranchNewsResource($branchNews);
    }

    public function store(RegionalBranch $regionalBranch, BranchNewsRequest $request)
    {
        DB::beginTransaction();

        try {
            foreach ($request->validated()['news'] as $newsData) {
                $image = $newsData['image_file'] ?? null;
                unset($newsData['image_file']);

                // Update if ID is present, otherwise create
                if (!empty($newsData['id'])) {
                    $branchNewsItem = $regionalBranch->news()->findOrFail($newsData['id']);
                    $branchNewsItem->update($newsData);
                } else {
                    $branchNewsItem = $regionalBranch->news()->create($newsData);
                }

                // If image is provided, replace existing media
                if ($image) {
                    $branchNewsItem->clearMediaCollection(BranchNews::IMAGE);
                    $branchNewsItem
                        ->addMedia($image)
                        ->toMediaCollection(BranchNews::IMAGE);
                }
            }

            DB::commit();

            return new BranchNewsResource($branchNewsItem);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to process news',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
