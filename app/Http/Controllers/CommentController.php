<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\JsonResponse;

class CommentController extends Controller
{
    public function destroy(Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return response()->json(['message' => 'Comment deleted successfully.']);
    }

    public function hide(Comment $comment): JsonResponse
    {
        $this->authorize('hide', $comment);

        $comment->update(['is_hidden' => true]);

        return response()->json(['message' => 'Comment hidden successfully.']);
    }
}
