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
        // ─── Import Form (pre-filled from vault product) ───
    public function importForm($purchaseId)
    {
        $purchase = \Workdo\ProductVault\Entities\VaultPurchase::where('id', $purchaseId)
            ->where('user_id', auth()->id())
            ->where('payment_status', 'approved')
            ->first();

        if (!$purchase || !$purchase->product) {
            return redirect()->route('vault-library.index')->with('error', __('Purchase not found or not approved.'));
        }

        if ($purchase->imported) {
            return redirect()->route('vault-library.edit-import', $purchaseId)->with('info', __('Product already imported. You can edit it.'));
        }

        $vaultProduct = $purchase->product;
        $storeId = $purchase->store_id;

        // Get store categories
        $categories = \App\Models\Category::where('store_id', $storeId)
            ->where('status', 1)
            ->pluck('name', 'id');

        return view('productvault::dashboard.import-form', compact('purchase', 'vaultProduct', 'categories', 'storeId'));
    }

    // ─── Process Import ───
    public function processImport(Request $request, $purchaseId)
    {
        $purchase = \Workdo\ProductVault\Entities\VaultPurchase::where('id', $purchaseId)
            ->where('user_id', auth()->id())
            ->where('payment_status', 'approved')
            ->first();

        if (!$purchase || !$purchase->product) {
            return redirect()->route('vault-library.index')->with('error', __('Purchase not found.'));
        }

        if ($purchase->imported) {
            return redirect()->route('vault-library.index')->with('error', __('Already imported.'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'detail' => 'nullable|string',
        ]);

        $vaultProduct = $purchase->product;
        $storeId = $purchase->store_id;

        // Create slug
        $slug = \Illuminate\Support\Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        while (\App\Models\Product::where('slug', $slug)->where('store_id', $storeId)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        // Create the product
        $product = \App\Models\Product::create([
            'name' => $request->name,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'description' => $request->description,
            'detail' => $request->detail,
            'cover_image_path' => $vaultProduct->preview_image,
            'cover_image_url' => $vaultProduct->preview_image ? asset($vaultProduct->preview_image) : null,
            'store_id' => $storeId,
            'created_by' => auth()->id(),
            'status' => 1,
            'product_type' => 'digital',
            'digital_type' => 'file',
            'downloadable_product' => $vaultProduct->file_path,
            'variant_product' => 0,
            'track_stock' => 0,
            'product_stock' => 999,
            'vault_purchase_id' => $purchase->id,
        ]);

        // Copy preview image to product_images if exists
        if ($vaultProduct->preview_image) {
            \App\Models\ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $vaultProduct->preview_image,
                'image_url' => $vaultProduct->preview_image ? asset($vaultProduct->preview_image) : null,
                'store_id' => $storeId,
            ]);
        }

        // Update purchase record
        $purchase->update([
            'imported' => 1,
            'imported_product_id' => $product->id,
            'imported_at' => now(),
        ]);

        return redirect()->route('vault-library.index')->with('success', __('Product imported successfully to your store!'));
    }

    // ─── Edit Imported Product ───
    
    /**
     * Process import - create product in tenant's store
     */
    public function importProduct(Request $request, $purchaseId)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        $storeId = $user->current_store;

        if (!$storeId) {
            $store = \App\Models\Store::where('created_by', $user->id)->first();
            if (!$store) {
                return redirect()->route('vault-library.index')
                    ->with('error', __('You do not have a store. Please create a store first.'));
            }
            $storeId = $store->id;
        }

        $purchase = \Workdo\ProductVault\Entities\VaultPurchase::where('id', $purchaseId)
            ->where('user_id', $user->id)
            ->where('payment_status', 'approved')
            ->where('imported', 0)
            ->firstOrFail();

        $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:products,slug',
            'category_id' => 'required|exists:categories,id',
            'price'       => 'required|numeric|min:0',
            'sale_price'  => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'detail'      => 'nullable|string|max:500',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:2048',
        ]);

        $vaultProduct = $purchase->product;
        if (!$vaultProduct) {
            return redirect()->route('vault-library.index')
                ->with('error', __('Original product not found.'));
        }

        $slug = $request->input('slug');
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($request->input('name'));
            $originalSlug = $slug;
            $counter = 1;
            while (\App\Models\Product::where('slug', $slug)->where('store_id', $storeId)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
        }

        $coverImagePath = null;
        if ($request->hasFile('cover_image') && $request->file('cover_image')->isValid()) {
            $uploadedFile = $request->file('cover_image');
            $fileName = time() . '_' . $uploadedFile->getClientOriginalName();
            $uploadDir = 'uploads/products/' . $storeId;
            $destinationPath = public_path($uploadDir);
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $uploadedFile->move($destinationPath, $fileName);
            $coverImagePath = $uploadDir . '/' . $fileName;
        } elseif ($vaultProduct->preview_image) {
            $sourcePath = public_path($vaultProduct->preview_image);
            if (file_exists($sourcePath)) {
                $uploadDir = 'uploads/products/' . $storeId;
                $destinationPath = public_path($uploadDir);
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $fileName = 'vault_' . basename($vaultProduct->preview_image);
                $counter = 1;
                $baseFile = pathinfo($fileName, PATHINFO_FILENAME);
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                while (file_exists($destinationPath . '/' . $fileName)) {
                    $fileName = $baseFile . '_' . $counter++ . '.' . $ext;
                }
                copy($sourcePath, $destinationPath . '/' . $fileName);
                $coverImagePath = $uploadDir . '/' . $fileName;
            }
        }

        $product = \App\Models\Product::create([
            'name'             => $request->input('name'),
            'slug'             => $slug,
            'category_id'      => $request->input('category_id'),
            'price'            => $request->input('price'),
            'sale_price'       => $request->input('sale_price', 0),
            'description'      => $request->input('description'),
            'detail'           => $request->input('detail'),
            'cover_image_path' => $coverImagePath,
            'status'           => 1,
            'store_id'         => $storeId,
            'created_by'       => $user->id,
            'stock_status'     => '0',
            'variant_product'  => 1,
            'track_stock'      => 0,
            'product_stock'    => 0,
            'low_stock_threshold' => 0,
            'trending'         => 0,
            'average_rating'   => 0,
        ]);

        $purchase->update([
            'imported'            => 1,
            'imported_product_id' => $product->id,
            'imported_at'         => now(),
        ]);

        return redirect()->route('vault-library.index')
            ->with('success', __('Product imported successfully to your store!'));
    }

    public function editImport($purchaseId)
    {
        $purchase = \Workdo\ProductVault\Entities\VaultPurchase::where('id', $purchaseId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$purchase || !$purchase->imported || !$purchase->imported_product_id) {
            return redirect()->route('vault-library.index')->with('error', __('Import not found.'));
        }

        $product = \App\Models\Product::find($purchase->imported_product_id);
        if (!$product) {
            return redirect()->route('vault-library.index')->with('error', __('Imported product not found.'));
        }

        $storeId = $purchase->store_id;
        $categories = \App\Models\Category::where('store_id', $storeId)
            ->where('status', 1)
            ->pluck('name', 'id');

        return view('productvault::dashboard.edit-import', compact('purchase', 'product', 'categories'));
    }

    // ─── Update Imported Product ───
    public function updateImport(Request $request, $purchaseId)
    {
        $purchase = \Workdo\ProductVault\Entities\VaultPurchase::where('id', $purchaseId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$purchase || !$purchase->imported || !$purchase->imported_product_id) {
            return redirect()->route('vault-library.index')->with('error', __('Import not found.'));
        }

        $product = \App\Models\Product::find($purchase->imported_product_id);
        if (!$product) {
            return redirect()->route('vault-library.index')->with('error', __('Imported product not found.'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'detail' => 'nullable|string',
        ]);

        $slug = \Illuminate\Support\Str::slug($request->name);
        $originalSlug = $slug;
        $counter = 1;
        while (\App\Models\Product::where('slug', $slug)->where('store_id', $product->store_id)->where('id', '!=', $product->id)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        $product->update([
            'name' => $request->name,
            'slug' => $slug,
            'category_id' => $request->category_id,
            'price' => $request->price,
            'sale_price' => $request->sale_price,
            'description' => $request->description,
            'detail' => $request->detail,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('vault-library.index')->with('success', __('Product updated successfully!'));
    }
}