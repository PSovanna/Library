<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn ($request, $e) => $request->is('api/*') || $request->expectsJson());
        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }
            if ($e instanceof ValidationException) {
                return response()->json(['success' => false, 'message' => 'Invalid input. Please correct the highlighted fields.', 'errors' => $e->errors()], 422);
            }
            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            $message = match ($status) {
                404 => 'Record or endpoint not found.',
                405 => 'This request method is not allowed.',
                413 => 'The uploaded picture is too large.',
                default => 'Unable to complete the request. Please try again.',
            };

            return response()->json(['success' => false, 'message' => $message], $status);
        });
    })->create();
