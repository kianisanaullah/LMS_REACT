<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        //  Check if user exists and isAdmin flag is set
        if (! $user || ! ($user->isAdmin ?? false)) {
            return response()->json([
                'error' => 'Unauthorized. Only Admin can manage roles.'
            ], 403);
        }

        return $next($request);
    }
}
