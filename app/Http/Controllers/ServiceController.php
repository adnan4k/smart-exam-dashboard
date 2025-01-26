<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    //
    public function index()
    {
        $service = Service::latest()->get();

        return response()->json($service);
    }
}
