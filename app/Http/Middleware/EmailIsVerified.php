<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;
use App\Models\{Setting, Utility};
use App\Facades\ModuleFacade as Module;

class EmailIsVerified
{
    /**
     * Specify the redirect route for the middleware.
     *
     * @param  string  $route
     * @return string
     */
    public static function redirectTo($route)
    {
        return static::class.':'.$route;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $redirectToRoute
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|null
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        $settings = getSuperAdminAllSetting();
        // SECURITY FIX (M-03): Default to 'off' when setting is missing (fail-safe).
        $emailVerification = $settings['email_verification'] ?? 'off';
        if($emailVerification === "on") {
            if (! $request->user() ||
                ($request->user() instanceof MustVerifyEmail &&
                ! $request->user()->hasVerifiedEmail())) {
                return $request->expectsJson()
                        ? abort(403, 'Your email address is not verified.')
                        : Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
            }
        }

        if (module_is_active('Duo2FA') && auth()->user() && auth()->user()->type != 'super admin') {
            $is_login = session()->get('is_login');
            $move_dashboard = session()->get('move_dashboard');
            if ($move_dashboard != 'true' && Module::has('Duo2FA') && Module::isEnabled('Duo2FA') && (!$is_login || $is_login == 'false')) {
                return redirect()->route('duo2fa.form');
            }
        }
        return $next($request);
    }
}
