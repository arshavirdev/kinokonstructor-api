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

    /**
     * Sync branch news: items with id are updated, items without id are created,
     * and existing news missing from the payload are deleted (empty array deletes all).
     */
    public function store(RegionalBranch $regionalBranch, BranchNewsRequest $request)
    {
        $newsList = $request->validated()['news'] ?? [];

        DB::beginTransaction();

        try {
            $keepIds = collect($newsList)->pluck('id')->filter()->all();

            // Delete news removed on the client (model delete also clears media)
            $regionalBranch->news()
                ->whereNotIn('id', $keepIds)
                ->get()
                ->each(fn (BranchNews $news) => $news->delete());

            foreach ($newsList as $newsData) {
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

            return BranchNewsResource::collection($regionalBranch->news()->with('media')->get());
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'error' => 'Failed to process news',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
