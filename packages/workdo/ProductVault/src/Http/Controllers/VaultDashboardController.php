<?php

namespace Workdo\ProductVault\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Workdo\ProductVault\Entities\VaultProduct;
use Workdo\ProductVault\Entities\VaultPurchase;

class VaultDashboardController extends Controller
{
    /**
     * Marketplace - list active products
     */
    public function index(Request $request)
    {
        $query = VaultProduct::where('status', 'active');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $categories = VaultProduct::where('status', 'active')
            ->select('category')->distinct()->orderBy('category')
            ->pluck('category')->filter()->values();

        $products = $query->orderBy('created_at', 'desc')->paginate(12);

        $purchasedIds = [];
        if (Auth::check()) {
            $purchasedIds = VaultPurchase::where('user_id', Auth::id())
                ->where('payment_status', 'approved')
                ->pluck('product_id')->toArray();
        }

        return view('productvault::dashboard.index', compact('categories', 'products', 'purchasedIds'));
    }

    /**
     * Product detail page
     */
    public function show($id)
    {
        $product = VaultProduct::where('id', $id)->where('status', 'active')->firstOrFail();

        $alreadyPurchased = false;
        if (Auth::check()) {
            $alreadyPurchased = VaultPurchase::where('user_id', Auth::id())
                ->where('product_id', $product->id)
                ->where('payment_status', 'approved')
                ->exists();
        }

        return view('productvault::dashboard.show', compact('product', 'alreadyPurchased'));
    }

    /**
     * Checkout page - show product + payment option
     */
    public function checkout($id)
    {
        $product = VaultProduct::findOrFail($id);

        // Check if already purchased
        $alreadyPurchased = VaultPurchase::where('user_id', Auth::id())
            ->where('product_id', $product->id)
            ->where('payment_status', 'approved')
            ->exists();

        if ($alreadyPurchased) {
            return redirect()->route('vault-library.index')
                ->with('success', 'You already own this product!');
        }

        // Get currency from store settings
        $storeId = session('current_store', 1);
        if (!is_numeric($storeId)) $storeId = 1;
        $storeId = (int) $storeId;

        $settings = DB::table('settings')
            ->where('store_id', $storeId)
            ->pluck('value', 'name')->toArray();

        $currency = $settings['CURRENCY'] ?? $settings['currency_symbol'] ?? '$';

        return view('productvault::dashboard.checkout', compact('product', 'currency'));
    }

    /**
     * Process checkout - free product or paid with receipt
     */
    public function processCheckout(Request $request, $id)
    {
        $product = VaultProduct::findOrFail($id);
        $user = Auth::user();
        $storeId = session('current_store', 1);
        if (!is_numeric($storeId)) $storeId = 1;
        $storeId = (int) $storeId;

        // FREE product - auto approve
        if (floatval($product->price) <= 0) {
            VaultPurchase::create([
                'user_id'        => $user->id,
                'product_id'     => $product->id,
                'store_id'       => $storeId,
                'price_paid'     => 0,
                'payment_type'   => 'free',
                'payment_status' => 'approved',
                'payer_name'     => $user->name ?? '',
                'payer_email'    => $user->email ?? '',
                'purchased_at'   => now(),
            ]);

            return redirect()->route('vault-library.index')
                ->with('success', 'Product added to your library!');
        }

        // PAID product - validate receipt
        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
            'notes'   => 'nullable|string|max:500',
        ]);

        $receiptPath = $request->file('receipt')->store('vault_receipts', 'public');

        VaultPurchase::create([
            'user_id'        => $user->id,
            'product_id'     => $product->id,
            'store_id'       => $storeId,
            'price_paid'     => $product->price,
            'payment_type'   => 'external',
            'payment_status' => 'pending',
            'payer_name'     => $user->name ?? '',
            'payer_email'    => $user->email ?? '',
            'receipt'        => $receiptPath,
            'notes'          => $request->notes,
            'purchased_at'   => now(),
        ]);

        return redirect()->route('vault-library.index')
            ->with('success', 'Receipt submitted! Your purchase is pending review.');
    }

    /**
     * User's library - purchased products
     */
    public function library(Request $request)
    {
        $purchases = VaultPurchase::where('user_id', Auth::id())
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('productvault::dashboard.library', compact('purchases'));
    }

    /**
     * Upload receipt for existing purchase
     */
    public function uploadReceipt(Request $request, $purchaseId)
    {
        $purchase = VaultPurchase::where('id', $purchaseId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,gif,pdf|max:5120',
        ]);

        $receiptPath = $request->file('receipt')->store('vault_receipts', 'public');

        $purchase->update([
            'receipt' => $receiptPath,
            'notes'   => $request->notes ?? $purchase->notes,
        ]);

        return redirect()->route('vault-library.index')
            ->with('success', 'Receipt uploaded successfully!');
    }
}