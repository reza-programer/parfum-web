<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Dipanggil server Komerce, keasliannya dicek lewat tanda tangan HMAC.
        $middleware->validateCsrfTokens(except: ['pembayaran/komerce/callback']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
