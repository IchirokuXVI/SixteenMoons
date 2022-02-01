<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;

class Language
{
    //Cannot check for route names, instead for full URLs
    protected $except = [
    ];

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $redirectToRoute
     * @return Response|RedirectResponse
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if (request()->cookie('language') !== null) {
            App::setLocale(Cookie::get('language'));
        } else {
            $response = $next($request);

            return $response->withCookie(cookie()->forever('language', 'en', null, null, false, false));
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through email verification.
     *
     * @param Request $request
     * @return bool
     */
    protected function inExceptArray($request)
    {
        //Copied from the VerifyCsrfToken middleware in vendor/laravel/framework/src/Illuminate/Foundation/Middleware/VerifyCsrfToken.php
        //With the addition of route names
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->fullUrlIs($except) || $request->is($except) || $request->route()->getName() == $except) {
                return true;
            }
        }

        return false;
    }
}
