<?php
/**
 * Fix ONLY the controller file
 * ضع في: C:\xampp\htdocs\takwincart\public\vault_fix_controller.php
 * افتح: http://localhost/takwincart/vault_fix_controller.php
 * ثم احذفه
 */

$base = dirname(__DIR__); // takwincart
$ctrlPath = $base . '/packages/workdo/ProductVault/src/Http/Controllers/ProductVaultAdminController.php';

$code = <<<'PHP'
<?php

namespace Workdo\ProductVault\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Workdo\ProductVault\Entities\VaultProduct;

class ProductVaultAdminController extends Controller
{
    public function index(Request $request)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

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

        $products = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories = VaultProduct::distinct()->pluck('category')->filter();

        return view('product-vault::admin.index', compact('products', 'categories'));
    }

    public function create()
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $categories = VaultProduct::distinct()->pluck('category')->filter();

        return view('product-vault::admin.create', compact('categories'));
    }

    public function store(Request $request)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'file_path' => 'required|file|max:51200',
            'demo_url' => 'nullable|url|max:500',
            'status' => 'required|in:active,draft',
            'is_featured' => 'nullable|boolean',
        ]);

        $data = $request->all();

        // Generate slug
        $slug = \Str::slug($request->name);
        $count = VaultProduct::where('slug', $slug)->count();
        if ($count > 0) {
            $slug = $slug . '-' . ($count + 1);
        }
        $data['slug'] = $slug;

        // Upload preview image
        if ($request->hasFile('preview_image')) {
            $image = $request->file('preview_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(storage_path('app/public/vault_images'), $imageName);
            $data['preview_image'] = 'vault_images/' . $imageName;
        }

        // Upload product file
        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/public/vault_files'), $fileName);
            $data['file_path'] = 'vault_files/' . $fileName;
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        $data['is_featured'] = $request->has('is_featured') ? true : false;
        $data['downloads_count'] = 0;
        $data['created_by'] = Auth::id();
        $data['workspace_id'] = 1;

        VaultProduct::create($data);

        return redirect()->route('product-vault.index')
            ->with('success', __('Product added successfully.'));
    }

    public function edit($id)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = VaultProduct::findOrFail($id);
        $categories = VaultProduct::distinct()->pluck('category')->filter();

        return view('product-vault::admin.edit', compact('product', 'categories'));
    }

    public function update(Request $request, $id)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string|max:500',
            'category' => 'required|string|max:100',
            'price' => 'required|numeric|min:0',
            'preview_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'file_path' => 'nullable|file|max:51200',
            'demo_url' => 'nullable|url|max:500',
            'status' => 'required|in:active,draft',
            'is_featured' => 'nullable|boolean',
        ]);

        $product = VaultProduct::findOrFail($id);
        $data = $request->except(['_token', '_method']);

        // Update slug if name changed
        $slug = \Str::slug($request->name);
        if ($slug !== $product->slug) {
            $count = VaultProduct::where('slug', $slug)->where('id', '!=', $id)->count();
            if ($count > 0) {
                $slug = $slug . '-' . ($count + 1);
            }
            $data['slug'] = $slug;
        }

        // Upload new preview image
        if ($request->hasFile('preview_image')) {
            if ($product->preview_image && Storage::exists('public/' . $product->preview_image)) {
                Storage::delete('public/' . $product->preview_image);
            }
            $image = $request->file('preview_image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move(storage_path('app/public/vault_images'), $imageName);
            $data['preview_image'] = 'vault_images/' . $imageName;
        }

        // Upload new file
        if ($request->hasFile('file_path')) {
            if ($product->file_path && Storage::exists('public/' . $product->file_path)) {
                Storage::delete('public/' . $product->file_path);
            }
            $file = $request->file('file_path');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->move(storage_path('app/public/vault_files'), $fileName);
            $data['file_path'] = 'vault_files/' . $fileName;
            $data['file_type'] = $file->getClientOriginalExtension();
            $data['file_size'] = $file->getSize();
        }

        $data['is_featured'] = $request->has('is_featured') ? true : false;

        $product->update($data);

        return redirect()->route('product-vault.index')
            ->with('success', __('Product updated successfully.'));
    }

    public function destroy($id)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = VaultProduct::findOrFail($id);

        if ($product->preview_image && Storage::exists('public/' . $product->preview_image)) {
            Storage::delete('public/' . $product->preview_image);
        }
        if ($product->file_path && Storage::exists('public/' . $product->file_path)) {
            Storage::delete('public/' . $product->file_path);
        }

        $product->delete();

        return redirect()->route('product-vault.index')
            ->with('success', __('Product deleted successfully.'));
    }

    public function show($id)
    {
        if (\Auth::user()->type != 'super admin') {
            return redirect()->back()->with('error', __('Permission denied.'));
        }

        $product = VaultProduct::findOrFail($id);

        return view('product-vault::admin.show', compact('product'));
    }
}
PHP;

$result = file_put_contents($ctrlPath, $code);

// Clear caches
try {
    require $base . '/vendor/autoload.php';
    $app = require_once $base . '/bootstrap/app.php';
    $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    \Artisan::call('cache:clear');
    \Artisan::call('view:clear');
    \Artisan::call('route:clear');
} catch(\Exception $e) {}
?>
<!DOCTYPE html>
<html dir="ltr">
<head><meta charset="UTF-8"><title>Controller Fix</title>
<style>
body{font-family:monospace;background:#111;color:#0f0;padding:20px;margin:0}
div{max-width:600px;margin:0 auto}
h2{color:#0f0}
p{padding:8px 0;border-bottom:1px solid #333}
</style>
</head>
<body><div>
<h2>Controller Fixed</h2>
<p>File: <?= htmlspecialchars($ctrlPath) ?></p>
<p>Result: <?= $result ? 'SUCCESS - ' . $result . ' bytes written' : 'FAILED' ?></p>
<p style="color:#ff0">DELETE: public/vault_fix_controller.php</p>
<p>Then go to: <a href="/takwincart/product-vault" style="color:#0ff">/product-vault</a></p>
</div></body>
</html>
