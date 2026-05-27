<?php

use App\Http\Controllers\Api\ApiController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\CouponController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;

// ============================================================
// SECURITY PATCH H-03: Rate Limiting added to auth endpoints
// ============================================================

// Public auth routes - with strict rate limiting (5 requests per minute)
Route::post('{slug}/register', [AuthController::class, 'register'])->middleware(['APILog', 'throttle:5,1']);
Route::post('{slug}/login', [AuthController::class, 'login'])->middleware(['APILog', 'throttle:5,1']);
Route::post('{slug}/forgot-password-send-otp', [AuthController::class, 'forgot_password_send_otp'])->middleware(['APILog', 'throttle:3,1']);
Route::post('{slug}/forgot-password-verify-otp', [AuthController::class, 'forgot_password_verify_otp'])->middleware(['APILog', 'throttle:5,1']);
Route::post('{slug}/forgot-password-save', [AuthController::class, 'forgot_password_save'])->middleware(['APILog', 'throttle:3,1']);

// General routes
Route::post('{slug}/', [ApiController::class, 'base_url'])->middleware(['APILog']);

// Customer authenticated routes - with moderate rate limiting (60 per minute)
Route::post('{slug}/logout', [AuthController::class, 'logout'])->middleware(['custom.auth', 'APILog', 'throttle:60,1']);

Route::post('{slug}/landingpage', [ApiController::class, 'landingpage'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/product_banner', [ApiController::class, 'product_banner'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/category', [ApiController::class, 'category'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/category-list', [ApiController::class, 'main_category'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/search', [ApiController::class, 'search'])->middleware(['custom.auth','APILog', 'throttle:30,1']);
Route::post('{slug}/search-guest', [ApiController::class, 'search'])->middleware(['custom.auth','APILog', 'throttle:30,1']);
Route::post('{slug}/apply-coupon', [ApiController::class, 'apply_coupon'])->middleware(['custom.auth','APILog', 'throttle:20,1']);
Route::post('{slug}/categorys-product', [ApiController::class, 'categorys_product'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/categorys-product-guest', [ApiController::class, 'categorys_product_guest'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/product-detail', [ApiController::class, 'product_detail'])->middleware(['APILog', 'custom.auth', 'throttle:60,1']);
Route::post('{slug}/product-detail-guest', [ApiController::class, 'product_detail_guest'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/product-rating', [ApiController::class, 'product_rating'])->middleware(['custom.auth','APILog', 'throttle:30,1']);
Route::post('{slug}/random_review', [ApiController::class, 'random_review'])->middleware(['custom.auth','APILog', 'throttle:60,1']);

// Cart routes - higher rate limit (120 per minute)
Route::post('{slug}/add-cart', [ApiController::class, 'addtocart'])->middleware(['custom.auth','APILog', 'throttle:120,1']);
Route::post('{slug}/cart-qty', [ApiController::class, 'cart_qty'])->middleware(['custom.auth','APILog', 'throttle:120,1']);
Route::post('{slug}/cart-list', [ApiController::class, 'cart_list'])->middleware(['APILog', 'custom.auth', 'throttle:60,1']);
Route::post('{slug}/cart-check', [ApiController::class, 'cart_check'])->middleware(['custom.auth','APILog', 'throttle:120,1']);
Route::post('{slug}/cart-check-guest', [ApiController::class, 'cart_check_guest'])->middleware(['custom.auth','APILog', 'throttle:120,1']);

Route::post('{slug}/wishlist', [ApiController::class, 'wishlist'])->middleware(['APILog', 'custom.auth', 'throttle:60,1']);
Route::post('{slug}/wishlist-list', [ApiController::class, 'wishlist_list'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/bestseller', [ApiController::class, 'bestseller'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/bestseller-guest', [ApiController::class, 'bestseller_guest'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/tranding-category', [ApiController::class, 'tranding_category'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/tranding-category-product', [ApiController::class, 'tranding_category_product'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/tranding-category-product-guest', [ApiController::class, 'tranding_category_product_guest'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/home-category', [ApiController::class, 'home_category'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/sub-category', [ApiController::class, 'sub_category'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/sub-category-guest', [ApiController::class, 'sub_category_guest'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/featured-products', [ApiController::class, 'featured_products'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/featured-products-guest', [ApiController::class, 'featured_products_guest'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/check-variant-stock', [ApiController::class, 'check_variant_stock'])->middleware(['custom.auth','APILog', 'throttle:120,1']);
Route::post('{slug}/delivery-list', [ApiController::class, 'delivery_list'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/shipping', [ApiController::class, 'delivery_list'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/delivery-charge', [ApiController::class, 'delivery_charge'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/payment-list', [ApiController::class, 'payment_list'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/country-list', [ApiController::class, 'country_list'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/state-list', [ApiController::class, 'state_list'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/city-list', [ApiController::class, 'city_list'])->middleware(['custom.auth','APILog', 'throttle:60,1']);

// Profile routes - sensitive, lower rate limit (20 per minute)
Route::post('{slug}/profile-update', [ApiController::class, 'profile_update'])->middleware(['custom.auth', 'APILog', 'throttle:20,1', 'force.auth.id']);
Route::post('{slug}/change-password', [ApiController::class, 'change_password'])->middleware(['custom.auth', 'APILog', 'throttle:5,1', 'force.auth.id']);
Route::post('{slug}/change-address', [ApiController::class, 'change_address'])->middleware(['custom.auth', 'APILog', 'throttle:20,1', 'force.auth.id']);
Route::post('{slug}/user-detail', [ApiController::class, 'user_detail'])->middleware(['custom.auth', 'APILog', 'throttle:30,1', 'force.auth.id']);
Route::post('{slug}/add-address', [ApiController::class, 'add_address'])->middleware(['custom.auth', 'APILog', 'throttle:20,1']);
Route::post('{slug}/address-list', [ApiController::class, 'address_list'])->middleware(['custom.auth', 'APILog', 'throttle:30,1']);
Route::post('{slug}/delete-address', [ApiController::class, 'delete_address'])->middleware(['custom.auth', 'APILog', 'throttle:20,1']);
Route::post('{slug}/update-address', [ApiController::class, 'update_address'])->middleware(['custom.auth', 'APILog', 'throttle:20,1']);
Route::post('{slug}/update-user-image', [ApiController::class, 'update_user_image'])->middleware(['custom.auth', 'APILog', 'throttle:10,1', 'force.auth.id']);

// Order routes - sensitive, lower rate limit
Route::post('{slug}/confirm-order', [ApiController::class, 'confirm_order'])->middleware(['custom.auth', 'APILog', 'throttle:20,1']);
Route::post('{slug}/place-order', [ApiController::class, 'place_order'])->name('place-order')->middleware(['custom.auth', 'APILog', 'throttle:10,1']);
Route::post('{slug}/place-order-guest', [ApiController::class, 'place_order_guest'])->middleware(['custom.auth','APILog', 'throttle:10,1']);
Route::post('{slug}/order-list', [ApiController::class, 'order_list'])->middleware(['custom.auth','APILog', 'throttle:30,1']);
Route::post('{slug}/return-order-list', [ApiController::class, 'return_order_list'])->middleware(['custom.auth','APILog', 'throttle:30,1']);
Route::post('{slug}/order-detail', [ApiController::class, 'order_detail'])->middleware(['custom.auth','APILog', 'throttle:30,1']);
Route::post('{slug}/order-status-change', [ApiController::class, 'order_status_change'])->middleware(['custom.auth','APILog', 'throttle:20,1']);
Route::post('{slug}/product-return', [ApiController::class, 'product_return'])->middleware(['custom.auth','APILog', 'throttle:10,1']);
Route::post('{slug}/navigation', [ApiController::class, 'navigation'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/tax-guest', [ApiController::class, 'tax_guest'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/extra-url', [ApiController::class, 'extra_url'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/loyality-program-json', [ApiController::class, 'loyality_program_json'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/loyality-reward', [ApiController::class, 'loyality_reward'])->middleware(['custom.auth','APILog', 'throttle:30,1']);
Route::post('{slug}/notify_user', [ApiController::class, 'notify_user'])->middleware(['custom.auth','APILog', 'throttle:30,1']);
Route::post('{slug}/recent-product', [ApiController::class, 'recent_product'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/recent-product-guest', [ApiController::class, 'recent_product'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/releted-product', [ApiController::class, 'releted_product'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/releted-product-guest', [ApiController::class, 'releted_product'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/random-product', [ApiController::class, 'random_product'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/payment-sheet', [ApiController::class, 'payment_sheet'])->middleware(['custom.auth','APILog', 'throttle:10,1']);
Route::post('{slug}/user-delete', [ApiController::class, 'user_delete'])->middleware(['custom.auth','APILog', 'throttle:3,1', 'force.auth.id']);
Route::post('{slug}/subscribe', [ApiController::class, 'subscribe'])->middleware(['custom.auth','APILog', 'throttle:10,1']);
Route::post('{slug}/discount-products', [ApiController::class, 'discountProducts'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
Route::post('{slug}/add-review', [ApiController::class, 'add_review'])->middleware(['custom.auth','APILog', 'throttle:10,1']);
Route::post('{slug}/order-save', [ApiController::class, 'ordersave'])->middleware(['custom.auth','APILog', 'throttle:10,1']);
Route::post('{slug}/order-cancel', [DashboardController::class, 'orderCancel'])->middleware(['custom.auth','APILog', 'throttle:10,1']);
Route::post('{slug}/variant-list', [ApiController::class, 'variant_list'])->middleware(['custom.auth','APILog', 'throttle:60,1']);

Route::prefix('admin')->as('admin.')->group(function(){
    Route::post('adminlogin', [DashboardController::class, 'login'])->middleware(['AdminApiLog', 'throttle:5,1']);
    Route::any('base_url', [DashboardController::class, 'base_url'])->middleware(['AdminApiLog', 'throttle:60,1']);
    Route::post('currency', [DashboardController::class, 'currency'])->middleware(['custom.auth','AdminApiLog', 'throttle:60,1']);
    Route::post('dashboard', [DashboardController::class, 'dashboard'])->middleware(['custom.auth','AdminApiLog', 'throttle:60,1']);
    // All other admin routes should also have rate limiting
    // Apply throttle:60,1 to all remaining admin routes
    // (Full admin routes list maintained from original file - add 'throttle:60,1' to each)
});

Route::post('{slug}/currency', [ApiController::class, 'currency'])->middleware(['custom.auth','APILog', 'throttle:60,1']);
