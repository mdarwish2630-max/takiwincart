<?php
/*
 * PV_FixImportV2.php
 * Fixes BOTH: missing importProduct() method + missing routes
 * Run: http://localhost/takwincart/PV_FixImportV2.php
 */

$base = __DIR__;
if (!is_dir($base . '/packages/workdo/ProductVault')) {
    $base = dirname(__DIR__);
}

$pkg      = $base . '/packages/workdo/ProductVault';
$ctrlFile = $pkg . '/src/Http/Controllers/VaultDashboardController.php';
$routeFile = $pkg . '/src/Routes/web.php';

$results = [];
$errs = [];
function ok($m) { global $results; $results[] = '<span style="color:green">&#10003;</span> ' . $m; }
function err($m) { global $errs; $errs[] = '<span style="color:red">&#10007;</span> ' . $m; }

// ============================================
// FIX 1: Add importProduct() to Controller
// ============================================
$ctrl = file_get_contents($ctrlFile);

if (strpos($ctrl, 'function importProduct') !== false) {
    ok('importProduct() already exists - skipped');
} else {
    // Find the importForm method and insert importProduct after it
    $importProductMethod = <<<'METHOD'

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

        // Generate slug if empty
        $slug = $request->input('slug');
        if (empty($slug)) {
            $slug = \Illuminate\Support\Str::slug($request->input('name'));
            $originalSlug = $slug;
            $counter = 1;
            while (\App\Models\Product::where('slug', $slug)->where('store_id', $storeId)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
        }

        // Handle cover image
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
                $baseName = pathinfo($fileName, PATHINFO_FILENAME);
                $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                while (file_exists($destinationPath . '/' . $fileName)) {
                    $fileName = $baseName . '_' . $counter++ . '.' . $ext;
                }
                copy($sourcePath, $destinationPath . '/' . $fileName);
                $coverImagePath = $uploadDir . '/' . $fileName;
            }
        }

        // Create the product in tenant's store
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

        // Update purchase record
        $purchase->update([
            'imported'            => 1,
            'imported_product_id' => $product->id,
            'imported_at'         => now(),
        ]);

        return redirect()->route('vault-library.index')
            ->with('success', __('Product imported successfully to your store!'));
    }
METHOD;

    // Insert after importForm method - find the end of importForm
    // Strategy: find "function importForm" then find next function and insert before it
    $importFormPos = strpos($ctrl, 'function importForm');
    if ($importFormPos !== false) {
        // Find the next function definition after importForm
        $afterImportForm = substr($ctrl, $importFormPos);
        // Look for the next "function " that's not inside the current method
        // Simple approach: find "function editImport" and insert before it
        $editImportPos = strpos($ctrl, 'function editImport');
        if ($editImportPos !== false) {
            // Insert importProduct before editImport
            $ctrl = substr($ctrl, 0, $editImportPos) . $importProductMethod . "\n" . substr($ctrl, $editImportPos);
            if (file_put_contents($ctrlFile, $ctrl)) {
                ok('Added importProduct() method to controller');
            } else {
                err('Failed to write controller file');
            }
        } else {
            // Fallback: append before last closing brace
            $lastBrace = strrpos($ctrl, '}');
            $ctrl = substr($ctrl, 0, $lastBrace) . $importProductMethod . "\n}\n";
            if (file_put_contents($ctrlFile, $ctrl)) {
                ok('Added importProduct() method (append fallback)');
            } else {
                err('Failed to write controller file');
            }
        }
    } else {
        err('Could not find importForm() in controller');
    }
}

// ============================================
// FIX 2: Add import routes to web.php
// ============================================
$routes = file_get_contents($routeFile);

if (strpos($routes, 'edit-import') !== false) {
    ok('Import routes already exist - skipped');
} else {
    // Find the last route in vault-library group and insert after it
    $target = "Route::post('/upload-receipt/{purchaseId}', [VaultDashboardController::class, 'uploadReceipt'])->name('upload-receipt');";
    
    $pos = strpos($routes, $target);
    if ($pos !== false) {
        $newRoutes = "
    // Import feature routes
    Route::get('/import/{purchase}', [VaultDashboardController::class, 'importForm'])->name('import-form');
    Route::post('/import/{purchase}', [VaultDashboardController::class, 'importProduct'])->name('import');
    Route::get('/edit-import/{purchase}', [VaultDashboardController::class, 'editImport'])->name('edit-import');
    Route::put('/update-import/{purchase}', [VaultDashboardController::class, 'updateImport'])->name('update-import');
";
        $insertPos = $pos + strlen($target);
        $routes = substr($routes, 0, $insertPos) . $newRoutes . substr($routes, $insertPos);
        
        if (file_put_contents($routeFile, $routes)) {
            ok('Added 4 import routes to web.php');
        } else {
            err('Failed to write route file');
        }
    } else {
        err('Could not find upload-receipt route in web.php');
    }
}

// ============================================
// VERIFY
// ============================================
$ctrl2 = file_get_contents($ctrlFile);
$routes2 = file_get_contents($routeFile);

echo '<div style="font-family:monospace;font-size:14px;line-height:2;">';
echo '<h2>PV_FixImportV2 - Results</h2>';
foreach ($results as $r) echo $r . '<br>';
foreach ($errs as $e) echo $e . '<br>';

echo '<hr><h3>Verification</h3>';
$checks = [
    'importForm()'       => strpos($ctrl2, 'function importForm') !== false,
    'importProduct()'    => strpos($ctrl2, 'function importProduct') !== false,
    'editImport()'       => strpos($ctrl2, 'function editImport') !== false,
    'updateImport()'     => strpos($ctrl2, 'function updateImport') !== false,
    'import-form route'  => strpos($routes2, 'import-form') !== false,
    'import route'       => strpos($routes2, "'import'") !== false,
    'edit-import route'  => strpos($routes2, 'edit-import') !== false,
    'update-import route'=> strpos($routes2, 'update-import') !== false,
];

$pass = 0;
foreach ($checks as $n => $c) {
    $pass += $c ? 1 : 0;
    echo ($c ? '<span style="color:green">&#10003;</span>' : '<span style="color:red">&#10007;</span>') . ' ' . $n . '<br>';
}
echo '<br><b>' . $pass . '/' . count($checks) . ' passed</b>';

echo '<hr><h3>Updated web.php</h3>';
echo '<pre style="font-size:11px;background:#1e293b;color:#e2e8f0;padding:10px;border-radius:8px;">' . htmlspecialchars($routes2) . '</pre>';
echo '</div>';
