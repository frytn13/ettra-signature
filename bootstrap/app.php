<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\PreventBackHistory;
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
        /*
        |--------------------------------------------------------------------------
        | Authentication Redirects
        |--------------------------------------------------------------------------
        |
        | Area internal Ettra Signature menggunakan halaman login Admin khusus.
        | Pengguna yang belum terautentikasi diarahkan ke admin.login, sedangkan
        | pengguna yang sudah login diarahkan kembali ke dashboard apabila mencoba
        | membuka halaman yang hanya ditujukan untuk guest.
        |
        */
        $middleware->redirectGuestsTo(
            fn (Request $request) => route('admin.login')
        );

        $middleware->redirectUsersTo(
            fn (Request $request) => route('admin.dashboard')
        );

        /*
        |--------------------------------------------------------------------------
        | Route Middleware Aliases
        |--------------------------------------------------------------------------
        |
        | Alias role digunakan untuk membatasi route berdasarkan role internal.
        | Contoh: ->middleware('role:owner') untuk halaman khusus Owner.
        |
        */
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
            'no.cache' => PreventBackHistory::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })
    ->create();
