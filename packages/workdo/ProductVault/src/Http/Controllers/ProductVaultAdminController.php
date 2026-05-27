<?php

namespace Workdo\ProductVault\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Workdo\ProductVault\Entities\VaultProduct;
use Workdo\ProductVault\Entities\VaultPurchase;

class ProductVaultAdminController extends Controller
{
    public function __construct()
    {
        // Add any middleware if needed
    }

    // ==========================================
    // PRODUCT CRUD
    // ==========================================

    public function index(Request $request)
    {
        $query = VaultProduct::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->paginate(15);
        $categories = VaultProduct::distinct()->pluck('category')->filter()->values();

        return view('productvault::admin.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = VaultProduct::distinct()->pluck('category')->filter()->values();
        return view('productvault::admin.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|max:2048',
            'file_path'   => 'nullable|file|max:102400',
            'payment_link'=> 'nullable|url|max:500',
            'status'      => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('vault-images', 'public');
        }

        if ($request->hasFile('file_path')) {
            $validated['file_path'] = $request->file('file_path')->store('vault-files', 'local');
        }

        $validated['slug'] = \Str::slug($request->name) . '-' . time();

        VaultProduct::create($validated);

        return redirect()->route('product-vault.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit($id)
    {
        $product = VaultProduct::findOrFail($id);
        $categories = VaultProduct::distinct()->pluck('category')->filter()->values();
        return view('productvault::admin.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $product = VaultProduct::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'category'    => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'image'       => 'nullable|image|max:2048',
            'file_path'   => 'nullable|file|max:102400',
            'payment_link'=> 'nullable|url|max:500',
            'status'      => 'required|in:active,inactive',
        ]);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('vault-images', 'public');
        }

        if ($request->hasFile('file_path')) {
            if ($product->file_path) {
                Storage::disk('local')->delete($product->file_path);
            }
            $validated['file_path'] = $request->file('file_path')->store('vault-files', 'local');
        }

        $product->update($validated);

        return redirect()->route('product-vault.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy($id)
    {
        $product = VaultProduct::findOrFail($id);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }
        if ($product->file_path) {
            Storage::disk('local')->delete($product->file_path);
        }

        $product->delete();

        return redirect()->route('product-vault.index')
            ->with('success', 'Product deleted.');
    }

    // ==========================================
    // PURCHASE MANAGEMENT
    // ==========================================

    public function purchasesIndex(Request $request)
    {
        $query = VaultPurchase::with(['user', 'product']);

        if ($request->filled('status')) {
            $query->where('payment_status', $request->status);
        }

        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        $purchases = $query->latest()->paginate(20);

        return view('productvault::admin.purchases.index', compact('purchases'));
    }

    public function purchaseShow($id)
    {
        $purchase = VaultPurchase::with(['user', 'product'])->findOrFail($id);

        return view('productvault::admin.purchases.show', compact('purchase'));
    }

    public function approvePurchase(Request $request, $id)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $purchase = VaultPurchase::findOrFail($id);

        $purchase->update([
            'payment_status' => 'approved',
            'admin_notes'    => $request->input('admin_notes', ''),
            'approved_at'    => now(),
            'rejected_at'    => null,
            'rejection_reason' => null,
        ]);

        return redirect()->back()
            ->with('success', 'Purchase approved successfully.');
    }

    public function rejectPurchase(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $purchase = VaultPurchase::findOrFail($id);

        $purchase->update([
            'payment_status'   => 'rejected',
            'rejection_reason' => $request->input('rejection_reason'),
            'rejected_at'      => now(),
            'approved_at'      => null,
        ]);

        return redirect()->back()
            ->with('success', 'Purchase rejected.');
    }
}
