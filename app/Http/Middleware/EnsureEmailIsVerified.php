<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified as Middleware;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureEmailIsVerified extends Middleware
{
    //Cannot check for route names, instead for full URLs
    protected $except = [
        'verification.notice',
        'verification.resend',
        'verification.verify',
        '/artisan/migrateFresh'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param Closure $next
     * @param  string|null  $redirectToRoute
     * @return Response|RedirectResponse
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        //Not check if the current route is in the except array
        if (!$this->inExceptArray($request) &&
            $request->user() &&
            ($request->user() instanceof MustVerifyEmail &&
            !$request->user()->hasVerifiedEmail())) {
            //If a user has not verified his email then automatically logout except in verification.notice which is defined in web.php
            auth()->logout();

            //Old middleware code
//            return $request->expectsJson()
//                ? abort(403, 'Your email address is not verified.')
//                : Redirect::route($redirectToRoute ?: 'verification.notice');
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
