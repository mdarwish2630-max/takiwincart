<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * SECURITY PATCH H-05: Force Authenticated User ID
 *
 * Prevents IDOR attacks by overriding customer_id and user_id
 * from request body with the authenticated user's ID.
 * This ensures customers can only access their own data.
 */
class ForceAuthUserId
{
    public function handle(Request $request, Closure $next)
    {
        $customer = auth('customers')->user();

        if ($customer) {
            // Force customer_id to match authenticated user - ignore external input
            if ($request->has('customer_id')) {
                $request->merge(['customer_id' => $customer->id]);
            }
            // Also handle user_id for consistency
            if ($request->has('user_id')) {
                $request->merge(['user_id' => $customer->id]);
            }
        }

        return $next($request);
    }
}
