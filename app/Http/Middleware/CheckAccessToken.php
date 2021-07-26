<?php namespace App\Http\Middleware;

use App\Http\Constants;
use App\Models\Settings;
use Carbon\Carbon;
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
        $settings = Settings::first();
        if (!$settings) {
            return redirect()->route('zoho-auth-init');
        }

        if (!Carbon::create($settings->expires_in)->isFuture()) {
            return redirect()->route('zoho-auth-init');
        }

        return $next($request);
    }
}
