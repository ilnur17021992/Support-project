<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RequestsLogger
{
    public function handle(Request $request, Closure $next)
    {
        Log::channel('requests')->info('API request:', [
            'method' => $request->method(),
            'path' => $request->path(),
            'ip' => $request->ip(),
            'user' => auth('sanctum')->id(),
            // 'headers' => $request->headers->all(),
            'data' => $request->all(),
        ]);

        $response = $next($request);

        Log::channel('requests')->info('API response:', [
            'status' => $response->status(),
            'data' => $response->content(),
        ]);

        return $response;
    }
}
