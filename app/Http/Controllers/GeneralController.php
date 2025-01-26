<?php

namespace App\Http\Controllers;

use App\Models\Biography;
use App\Models\Blog;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeneralController extends Controller
{
    //
    public function articles()
    {
        $articles = Service::latest()->get();
        return response()->json($articles);
    }
    public function blogs()
    {
        $blogs = Blog::with('category')->latest()->get();
        return response()->json($blogs);
    }
    public function biography(){
        $biography  = Biography::latest()->paginate(1);
        return response()->json($biography);
    }
     
    public function getBlogById(Request $request){
           $request->validate(['id'=>'required']); 
           $blog = Blog::with('category')->where('id',$request->id)->get();
           if(!$blog){
            return response()->json('blog not found',status:404);
           }
        return response()->json($blog);
    }
    public function blogsByCategory(Request $request)
    {

        if ($request->category) {
            $categoryName = $request->category_name;
            $blogs = Blog::whereHas('category', function ($query) use ($categoryName) {
                $query->where('name', $categoryName);
            })->get();
        }
    }
}
