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
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\OptimizeResponseHeaders::class,
        ]);

        // spatie/laravel-permission নিজে থেকে এই alias গুলো রেজিস্টার করে না।
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'doctor.profile' => \App\Http\Middleware\EnsureDoctorProfile::class,
        ]);

        // পেমেন্ট গেটওয়ে থেকে success/fail/cancel/webhook — সবই গেটওয়ের সার্ভার থেকে
        // ফেরত আসে (কখনো auto-submit POST ফর্ম দিয়ে), তাই আমাদের session CSRF টোকেন থাকে না।
        $middleware->validateCsrfTokens(except: [
            'second-opinion/payments/*',
        ]);

        // এই অ্যাপে কোনো routes/auth.php নেই ('login' নামের route নেই) — Filament তার নিজস্ব
        // /admin/login আলাদাভাবে হ্যান্ডেল করে, তাই 'auth' মিডলওয়্যার শুধু ডাক্তার ও ফিল্ড পোর্টালে ব্যবহৃত হয়।
        $middleware->redirectGuestsTo(fn ($request) => $request->is('field*') ? route('field.login') : route('doctor.login'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
