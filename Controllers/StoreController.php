<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Store;
use App\Models\User;
use App\Models\{Plan, PlanOrder};
use Illuminate\Support\Facades\Auth;
use App\Models\Utility;
use Illuminate\Support\Facades\Hash;
use App\Models\Role;
use App\Models\Page;
use App\Models\AppSetting;
use App\Models\Blog;
use App\Models\Order;
use App\Models\Tax;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Coupon;
use App\Models\Newsletter;
use App\Models\Testimonial;
use App\Models\Setting;
use App\Models\Shipping;
use App\Models\ProductVariant;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Lab404\Impersonate\Impersonate;
use Session;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Cache;
use App\DataTables\StoreDataTable;

class StoreController extends Controller
{
    // ... (all other methods remain the same as original)

    // Only the changeStore method is shown here with the fix.
    // Copy this method to replace the existing one.

    /**
     * SECURITY PATCH H-07: Fixed assignment vs comparison bug
     * Original: $store->is_active = 1  (assignment - always true)
     * Fixed:    $store->is_active == 1  (comparison - checks actual value)
     */
    public function changeStore($id)
    {
        $user = auth()->user();

        // Verify the store belongs to this user or user has access
        $store = Store::where('id', $id)
            ->where(function ($query) use ($user) {
                $query->where('created_by', $user->id)
                      ->orWhere('id', $user->current_store);
            })
            ->first();

        if (!$store) {
            return redirect()->back()->with('error', __('Store not found.'));
        }

        // FIXED: Use == (comparison) instead of = (assignment)
        if ($store->is_active == 1) {
            $user->current_store = $id;
            $user->update();
            return redirect()->back()->with('success', __('Store Change Successfully!'));
        } else {
            return redirect()->back()->with('error', __('Store is locked'));
        }
    }

    // ... (all other methods remain unchanged)
}
