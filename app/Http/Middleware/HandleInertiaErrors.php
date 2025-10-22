<?php

namespace App\Http\Middleware;

use Inertia\Inertia;
use Throwable;
use Closure;

class HandleInertiaErrors
{
    public function handle($request, Closure $next)
    {
        try {
            return $next($request);
        } catch (Throwable $e) {
            if ($request->inertia()) {
                return Inertia::render('Error', [
                    'status' => 500,
                    'message' => $e->getMessage(),
                ])->toResponse($request)->setStatusCode(500);
            }

            throw $e;
        }
    }
}
