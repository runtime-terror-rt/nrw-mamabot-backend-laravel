<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class UpdateLastSeen
{
   public function handle(Request $request, Closure $next)
{
    \Log::info('🔥 MIDDLEWARE ENTERED');

    if (auth()->check()) {
        \Log::info('✅ AUTH TRUE: ' . auth()->id());

        auth()->user()->update([
            'last_seen' => now(),
        ]);

        \Log::info('✅ LAST SEEN UPDATED');
    } else {
        \Log::info('❌ AUTH FALSE');
    }

    return $next($request);
}


}

