<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsValidated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user instanceof User && ! $user->is_validated) {
            return response()->json([
                'message' => 'Your account is pending approval. Please wait for an administrator to validate your account.',
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
