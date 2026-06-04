<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ScopeToJurisdiction
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            // Store jurisdiction context in request for use by controllers/policies
            $request->merge([
                '_jurisdiction_type' => $user->jurisdiction_type,
                '_jurisdiction_id'   => $user->jurisdiction_id,
            ]);
        }

        return $next($request);
    }
}
