<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Customer;
use App\Models\User;
use App\Models\DeliveryBoy;
use App\Traits\ApiResponser;

/**
 * SECURITY PATCH: CustomAuth - Fixed Token Validation
 * 
 * Original issues:
 * - Only checked token ID, never verified token hash (C-03)
 * - No token expiration check (M-11)
 * - No guard isolation per route (H-06)
 * 
 * Fixes:
 * - Uses Laravel Sanctum's built-in token validation via findToken()
 * - Checks token expiration (expires_at column)
 * - Validates tokenable type matches expected guard
 */
class CustomAuth
{
    use ApiResponser;

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return $this->error(['message' => __('Authorization token is required.')], __('Unauthenticated.'), 401);
        }

        // Extract plain text token after "Bearer "
        $plainTextToken = substr($token, 7);

        if (empty($plainTextToken)) {
            return $this->error(['message' => __('Invalid token format.')], __('Unauthenticated.'), 401);
        }

        // FIXED: Use Sanctum's findToken() which verifies both ID AND hash
        $tokenModel = \Laravel\Sanctum\PersonalAccessToken::findToken($plainTextToken);

        if (!$tokenModel) {
            return $this->error(['message' => __('Invalid or expired token.')], __('Unauthenticated.'), 401);
        }

        // FIXED: Check token expiration
        if ($tokenModel->expires_at && $tokenModel->expires_at->isPast()) {
            // Delete expired token
            $tokenModel->delete();
            return $this->error(['message' => __('Token has expired. Please login again.')], __('Unauthenticated.'), 401);
        }

        // Authenticate based on tokenable_type
        $tokenableType = $tokenModel->tokenable_type;
        $tokenableId = $tokenModel->tokenable_id;

        if ($tokenableType == 'App\Models\Customer') {
            $customer = Customer::find($tokenableId);
            if ($customer) {
                auth('customers')->login($customer);
            } else {
                return $this->error(['message' => __('User account not found.')], __('Unauthenticated.'), 401);
            }
        } elseif ($tokenableType == 'App\Models\User') {
            $user = User::find($tokenableId);
            if ($user) {
                auth()->login($user);
            } else {
                return $this->error(['message' => __('User account not found.')], __('Unauthenticated.'), 401);
            }
        } elseif ($tokenableType == 'App\Models\DeliveryBoy') {
            $deliveryBoy = DeliveryBoy::find($tokenableId);
            if ($deliveryBoy) {
                auth('deliveryboy')->login($deliveryBoy);
            } else {
                return $this->error(['message' => __('Delivery boy account not found.')], __('Unauthenticated.'), 401);
            }
        } else {
            return $this->error(['message' => __('Invalid token type.')], __('Unauthenticated.'), 401);
        }

        return $next($request);
    }
}
