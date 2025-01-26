<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;
use App\Models\Vacancy;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    //
    public function vacancy()
    {
        $vacancy = Vacancy::latest()->get();
        return response()->json($vacancy);
        
    }
    public function scholarship(){
        $scholarship = Scholarship::latest()->get();
        return response()->json($scholarship);
    }
}
