<?php

namespace App\Http\Controllers;

use App\Models\Type;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function examType(): JsonResponse
    {
        $types = Type::all();

        return response()->json([
            'status' => true,
            'data' => $types
        ]);
    }
}
