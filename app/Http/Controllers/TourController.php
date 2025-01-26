<?php

namespace App\Http\Controllers;

use App\Models\Tour;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TourController extends Controller
{
    //


    public function tourById($id){
      $tour = Tour::where('id',$id)->with('schedules')->latest()->get();
      return response()->json($tour);
    }

    public function getHomePackages()
    {
        $schedules = Tour::orderBy('id', 'desc')
            ->with('schedules')
            ->take(3)
            ->get();
        return response()->json([
            'data' => $schedules,
            'message' => 'success',
            'status' => 200
        ]);
    }

    public function getPackages()
    {
        $sections = Tour::orderBy('id', 'desc')
         ->with('schedules')
            ->get();
            Log::info($sections);
        return response()->json([
            'data' => $sections,
            'message' => 'success',
            'status' => 200
        ]);
    }
}
