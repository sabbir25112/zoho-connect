<?php namespace App\Http\Middleware;

use App\Http\Constants;
use Closure;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CheckAccessToken
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
        if (!Cache::has(Constants::ZOHO_ACCESS_KEY_CACHE)) {
            return redirect()->route('zoho-auth-init');
        }
        return $next($request);
    }
}
