<?php

use App\Http\Middleware\CmsMaintenance;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\SecureHeaders;
use App\Http\Middleware\TrackPageView;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureUserIsAdmin::class,
            'track.visit' => TrackPageView::class,
        ]);

        $middleware->trustProxies(headers: Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO);
        $middleware->append(SecureHeaders::class);
        $middleware->web(append: [CmsMaintenance::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['cf-turnstile-response', 'TURNSTILE_SECRET_KEY', 'turnstile_secret_key']);
    })->create();
