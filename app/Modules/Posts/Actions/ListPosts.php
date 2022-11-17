<?php

namespace App\Modules\Posts\Actions;

use App\Models\Post;

class ListPosts
{
    public function list($count): array|\Illuminate\Pagination\LengthAwarePaginator|\LaravelIdea\Helper\App\Models\_IH_AppModel_C
    {
        return Post::query()->orderBy('created_at', 'desc')->paginate($count);
    }
}
