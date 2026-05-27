<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'setlocate' => \App\Http\Middleware\SetLocate::class,
            'themelanguage' => \App\Http\Middleware\ThemeLanguage::class,
            'APILog' => \App\Http\Middleware\APILog::class,
            'AdminApiLog' => \App\Http\Middleware\AdminApiLog::class,
            'xss' => \App\Http\Middleware\XSS::class,
            'activeTheme' => \App\Http\Middleware\ActiveTheme::class,
            'custom.auth' => \App\Http\Middleware\CustomAuth::class,
            'PlanModuleCheck' => \App\Http\Middleware\PlanModuleCheck::class,
            'verified' => \App\Http\Middleware\EmailIsVerified::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            '2fa' => \PragmaRX\Google2FALaravel\Middleware::class,
            'demo_mode' => \App\Http\Middleware\DemoModeMiddleware::class,
            // SECURITY PATCH H-05: New middleware to prevent IDOR
            'force.auth.id' => \App\Http\Middleware\ForceAuthUserId::class,
        ]);
        $middleware->appendToGroup('web', \App\Http\Middleware\SetLocate::class);

        // SECURITY PATCH H-06: Reduced CSRF exemptions
        // Only actual payment callback routes are exempted
        // Removed: */checkout, /login, /duologin, join_us/store/
        $middleware->validateCsrfTokens(
            except: [
                'iyzipay/callback/*',
                '/aamarpay/*',
                '*/aamarpay/callback?*',
                '*/get-payment-paytm',
                '*/get-payment-aamarpay?*',
                '*/get-payment-iyzico',
                'plan-get-phonepe-status',
                '*/store-payment-phonepe',
                '*/get-donation-payment-paytm',
                '*/get-donation-payment-iyzipay',
                '*/get-donation-payment-aamarpay',
                'plan-easebuzz-payment-notify*',
                '*get-payment-easebuzz*',
                'plan-get-Powertranz-status*',
                'store-get-Powertranz-status*',
                'campaigns-card-payment/',
                'campaigns-Powertranz',
                'plan-payfast-payment',
                '/plan/sslcommerz/status/*',
                '*/get-payment-sslcommerz',
                '*/get-ticket-payment-paytm',
                '*/get-ticket-payment-aamarpay',
            ]
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
