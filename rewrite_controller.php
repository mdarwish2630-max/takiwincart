<?php
/**
 * rewrite_controller.php
 * Full rewrite of VaultMarketplaceController - clean, no store check
 */

$base = 'C:\\xampp\\htdocs\\takwincart';
$ctrl = $base . '\\packages\\workdo\\ProductVault\\src\\Http\\Controllers\\VaultMarketplaceController.php';

$newController = <<<'PHP'
<?php

namespace Workdo\ProductVault\Http\Controllers;

use Workdo\ProductVault\Entities\VaultProduct;
use Workdo\ProductVault\Entities\VaultPurchase;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Notification;
use Workdo\ProductVault\Notifications\PurchaseNotification;

class VaultMarketplaceController extends Controller
{
    public function index(Request $request)
    {
        $query = VaultProduct::where('status', 'active');

        // Category filter
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->orderBy('created_at', 'desc')->paginate(12);

        // Get categories for filter
        $categories = VaultProduct::where('status', 'active')
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category');

        // Get purchased product IDs for current user
        $purchasedIds = VaultPurchase::where('user_id', Auth::id())
            ->where('payment_status', 'approved')
            ->pluck('product_id')
            ->toArray();

        return view('productvault::merchant.marketplace', compact('products', 'categories', 'purchasedIds'));
    }

    public function show($id)
    {
        $product = VaultProduct::findOrFail($id);
        return view('productvault::merchant.show', compact('product'));
    }

    public function library()
    {
        $purchases = VaultPurchase::where('user_id', Auth::id())
            ->where('payment_status', 'approved')
            ->orderBy('purchased_at', 'desc')
            ->paginate(10);

        $pendingPurchases = VaultPurchase::where('user_id', Auth::id())
            ->where('payment_status', 'pending')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('productvault::merchant.library', compact('purchases', 'pendingPurchases'));
    }

    public function checkout($id)
    {
        $product = VaultProduct::findOrFail($id);

        // Read enabled payment methods from settings table
        $enabledPayments = [];
        $paymentMethods = [
            'bank_transfer' => 'Bank Transfer',
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'paystack' => 'Paystack',
            'razorpay' => 'Razorpay',
            'flutterwave' => 'Flutterwave',
            'paytm' => 'PayTM',
            'mollie' => 'Mollie',
            'midtrans' => 'Midtrans',
            'paymentwall' => 'Paymentwall',
        ];

        foreach ($paymentMethods as $key => $label) {
            $row = DB::table('settings')
                ->where('name', 'is_' . $key . '_enabled')
                ->where('value', 'on')
                ->first();
            if ($row) {
                $enabledPayments[$key] = ['key' => $key, 'name' => $label];
            }
        }

        // Read bank transfer details from settings
        $bankDetails = DB::table('settings')
            ->where('name', 'bank_transfer')
            ->value('value') ?? '';

        return view('productvault::merchant.checkout', compact('product', 'enabledPayments', 'bankDetails'));
    }

    public function processCheckout(Request $request, $id)
    {
        $request->validate([
            'payer_name' => 'required|string|max:255',
            'payer_email' => 'required|email|max:255',
            'payment_type' => 'required|string',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $product = VaultProduct::findOrFail($id);

        $purchase = new VaultPurchase();
        $purchase->product_id = $product->id;
        $purchase->user_id = Auth::id();
        $purchase->store_id = Auth::user()->current_store ?? 0;
        $purchase->price_paid = $product->price;
        $purchase->payment_type = $request->payment_type;
        $purchase->payer_name = $request->payer_name;
        $purchase->payer_email = $request->payer_email;
        $purchase->notes = $request->notes;

        if ($product->price <= 0) {
            $purchase->payment_type = 'free';
            $purchase->payment_status = 'approved';
            $purchase->purchased_at = now();
            $purchase->approved_at = now();
        } else {
            $purchase->payment_status = 'pending';
            $purchase->purchased_at = now();

            // Handle receipt upload
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')->store('vault_receipts', 'public');
                $purchase->receipt = $receiptPath;
            }
        }

        $purchase->save();

        // Notify admin
        try {
            $admin = \App\Models\User::where('type', 'super admin')->first();
            if ($admin) {
                Notification::send($admin, new PurchaseNotification($purchase, $product));
            }
        } catch (\Exception $e) {
            // Notification failed, continue
        }

        if ($product->price <= 0) {
            return redirect()->route('vault-library.index')->with('success', __('Product added to your library!'));
        }

        return redirect()->route('vault-library.index')->with('success', __('Purchase request submitted! Waiting for admin review.'));
    }

    public function import($id)
    {
        $purchase = VaultPurchase::where('id', $id)
            ->where('user_id', Auth::id())
            ->where('payment_status', 'approved')
            ->where('imported', 0)
            ->firstOrFail();

        $product = VaultProduct::findOrFail($purchase->product_id);

        // Import product to user's store
        $store = \App\Models\Store::find(Auth::user()->current_store ?? 0);

        if (!$store) {
            return redirect()->back()->with('error', __('You need a store to import products.'));
        }

        // Create product in user's store
        $newProduct = new \App\Models\Product();
        $newProduct->name = $product->name;
        $newProduct->slug = Str::slug($product->name) . '-' . time();
        $newProduct->short_description = $product->short_description ?? '';
        $newProduct->description = $product->description ?? '';
        $newProduct->price = $product->price;
        $newProduct->store_id = $store->id;
        $newProduct->created_by = Auth::id();
        $newProduct->status = 'active';
        $newProduct->product_type = 'digital';

        if ($product->cover_image) {
            $newProduct->cover_image = $product->cover_image;
        }

        $newProduct->save();

        // Mark as imported
        $purchase->imported = 1;
        $purchase->imported_product_id = $newProduct->id;
        $purchase->imported_at = now();
        $purchase->save();

        return redirect()->route('vault-library.index')->with('success', __('Product imported to your store!'));
    }
}
PHP;

$bytes = file_put_contents($ctrl, $newController);
echo "Written $bytes bytes to VaultMarketplaceController.php\n";

// Clear caches
$vd = $base . '\\storage\\framework\\views\\';
$cl = 0;
if (is_dir($vd)) {
    foreach (glob($vd . '*') as $f) {
        if (is_file($f)) { unlink($f); $cl++; }
    }
}

$cd = $base . '\\storage\\framework\\cache\\';
$cc = 0;
if (is_dir($cd)) {
    foreach (glob($cd . '*') as $f) {
        if (is_file($f)) { unlink($f); $cc++; }
    }
}

echo "Cleared $cl views, $cc cache files.\n";
echo "\nDONE! Controller fully rewritten - no store check, clean syntax.\n";
echo "Try vault-marketplace now.\n";
