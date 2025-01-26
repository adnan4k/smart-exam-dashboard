<?php

namespace App\Http\Controllers;

use App\Models\Podcast;
use Illuminate\Http\Request;

class PodcastController extends Controller
{
    //
    public function index()
    {
        $podcasts = Podcast::latest()->get();
        return response()->json($podcasts);
    }
    public function latestPodcasts()
    {
        $podcasts = Podcast::latest()->paginate(3);
        return response()->json($podcasts);

    }
    public function allPodcasts(){
        $podcasts = Podcast::paginate(3);
        return response()->json($podcasts);
    }
}
