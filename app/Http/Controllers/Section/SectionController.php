<?php

namespace App\Http\Controllers\Section;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Section;
use App\Models\Service;
use App\Models\Testimony;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SectionController extends Controller
{
    //

    public function getHeroSlider()
    {
        $sections = Service::orderBy('id', 'asc')
            ->take(5)
            ->get();
        return response()->json([
            'data' => $sections,
            'message' => 'success',
            'status' => 200
        ]);
    }
    public function getSliderSection(){
           $sliders = Service::latest()->take(5)->get();

              return response()->json([
                'data' => $sliders,
                'message' => 'success',
                'status' => 200
              ]);
              
    }
    public function getHomeDestinations()
    {
        $sections = Section::orderBy('id', 'desc')
            ->where('type', 'destination')
            ->select('id', 'title', 'image')
            ->take(5)
            ->get();
        return response()->json([
            'data' => $sections,
            'message' => 'success',
            'status' => 200
        ]);
    }

    public function getAllDestionations()
    {
        $sections = Section::orderBy('id', 'desc')
            ->where('type', 'destination')
            ->select('id', 'title', 'content', 'image')
            ->get();
        return response()->json([
            'data' => $sections,
            'message' => 'success',
            'status' => 200
        ]);
    }

    public function getHomePackages()
    {
        $sections = Section::orderBy('id', 'desc')
            ->where('type', 'package')
            ->take(3)
            ->get();
        return response()->json([
            'data' => $sections,
            'message' => 'success',
            'status' => 200
        ]);
    }

    public function getPackages()
    {
        $sections = Section::orderBy('id', 'desc')
            ->where('type', 'package')
            ->get();
        return response()->json([
            'data' => $sections,
            'message' => 'success',
            'status' => 200
        ]);
    }

    public function getPackageDetail($id)
    {
        $section = Section::find($id);
        if (!$section) {
            return response()->json([
                'data' => [],
                'message' => 'No data found',
                'status' => 404
            ]);
        }
        return response()->json([
            'data' => $section,
            'message' => 'success',
            'status' => 200
        ]);
    }
    public function getDestinationDetail($id)
    {
        $section = Section::find($id);
        if (!$section) {
            return response()->json([
                'data' => [],
                'message' => 'No data found',
                'status' => 404
            ]);
        }
        return response()->json([
            'data' => $section,
            'message' => 'success',
            'status' => 200
        ]);
    }

    public function getTestimonials()
    {
        $testimonials = Testimony::orderBy('id', 'desc')
            ->get();
        return response()->json([
            'data' => $testimonials,
            'message' => 'success',
            'status' => 200
        ]);
    }

    public function getBlogDetail($id)
    {
        $blogs = Blog::find($id);
        if (!$blogs) {
            return response()->json([
                'data' => [],
                'message' => 'No data found',
                'status' => 404
            ]);
        }
        return response()->json([
            'data' => $blogs,
            'message' => 'success',
            'status' => 200
        ]);
    }
}
