<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Set headers through the bag rather than ->header(): file and stream
        // responses (e.g. video downloads) are Symfony responses without it.
        $response->headers->set('Access-Control-Allow-Origin', '*'); // Use specific origins for better security
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Headers', 'X-Requested-With,Content-Type,X-Token-Auth,Authorization');
        $response->headers->set('Access-Control-Expose-Headers', 'Content-Length,Content-Range,Accept-Ranges,X-Checksum-MD5,X-Uncompressed-Size');

        return $response;
    }
}
