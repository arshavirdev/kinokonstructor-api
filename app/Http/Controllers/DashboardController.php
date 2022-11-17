<?php

namespace App\Http\Controllers;

use App\Http\Resources\NewsResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\ProjectBriefResource;
use App\Models\News;
use App\Models\Post;
use App\Models\Project;
use Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $profile = Auth::user()->profile->id;
        $news = News::query()->orderBy('created_at', 'desc')->with(['media'])->take(5)->get();
        $posts = Post::query()->orderBy('created_at', 'desc')->with(['media'])->take(5)->get();
        $projects = Project::query()->orderBy('created_at', 'desc')->take(5)->get();
        return [
            'news' => NewsResource::collection($news),
            'posts' => PostResource::collection($posts),
            'projects' => ProjectBriefResource::collection($projects),
        ];
    }
}
