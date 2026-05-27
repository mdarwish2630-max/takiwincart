<?php
/*
 * PV_FixController.php
 * Fixes the broken controller file - removes duplicate modifiers
 * Run: http://localhost/takwincart/PV_FixController.php
 */

$base = __DIR__;
if (!is_dir($base . '/packages/workdo/ProductVault')) {
    $base = dirname(__DIR__);
}

$ctrlFile = $base . '/packages/workdo/ProductVault/src/Http/Controllers/VaultDashboardController.php';

echo '<div style="font-family:monospace;font-size:13px;line-height:1.8;">';
echo '<h2>PV_FixController - Fix Broken Controller</h2>';

$ctrl = file_get_contents($ctrlFile);

// Show the broken area
$brokenPos = strpos($ctrl, 'Multiple access type modifiers');
if ($brokenPos !== false) {
    echo '<p style="color:red;">Error text found in file (unlikely)</p>';
}

// Show lines around line 296
$lines = explode("\n", $ctrl);
echo '<h3>Lines 285-310 (around the error):</h3>';
echo '<pre style="background:#fef2f2;color:#991b1b;padding:10px;border-radius:8px;font-size:12px;border:1px solid #fca5a5;">';
for ($i = 284; $i < min(310, count($lines)); $i++) {
    $lineNum = $i + 1;
    $prefix = ($lineNum >= 290 && $lineNum <= 300) ? '<span style="color:red;font-weight:bold">' : '<span style="color:gray">';
    echo $prefix . str_pad($lineNum, 4) . ':</span> ' . htmlspecialchars($lines[$i]) . "\n";
}
echo '</pre>';

// Fix strategy: remove the entire importProduct block and re-add it properly
// First, find where the mess starts and ends

// The mess starts at "public \n    /**" (the dangling public before importProduct)
// Find "public \n    /**\n     * Process import"
$messStart = strpos($ctrl, "public \n    /**\n     * Process import");
if ($messStart === false) {
    // Try alternate patterns
    $messStart = strpos($ctrl, "public\n    /**\n     * Process import");
}
if ($messStart === false) {
    $messStart = strpos($ctrl, "public \r\n    /**");
}
if ($messStart === false) {
    // Broad search - find the comment and look backwards for the stray "public"
    $commentPos = strpos($ctrl, '* Process import - create product');
    if ($commentPos !== false) {
        // Go backwards to find "public" that's on its own line
        $searchBack = substr($ctrl, 0, $commentPos);
        $lastPublic = strrpos($searchBack, 'public');
        if ($lastPublic !== false) {
            // Check if this "public" is stray (followed by whitespace/newline, not " function")
            $afterPublic = substr($ctrl, $lastPublic, 20);
            if (preg_match('/^public[\s\r\n]+\/\*\*/', $afterPublic)) {
                $messStart = $lastPublic;
            }
        }
    }
}

if ($messStart === false) {
    echo '<p style="color:red;">Cannot find the broken section automatically.</p>';
    echo '<p>Showing full file for manual inspection:</p>';
    echo '<pre style="font-size:11px;background:#1e293b;color:#e2e8f0;padding:10px;border-radius:8px;max-height:500px;overflow:auto;">' . htmlspecialchars($ctrl) . '</pre>';
    die();
}

echo '<p style="color:green;">Found broken section at position: ' . $messStart . '</p>';

// Now find where the importProduct function ends (before "function editImport")
// Find "function editImport" after the mess start
$editImportPos = strpos($ctrl, 'function editImport', $messStart);
if ($editImportPos === false) {
    echo '<p style="color:red;">Cannot find editImport to determine end of broken section.</p>';
    die();
}

// Now rebuild: everything before messStart + proper importProduct + everything from editImport onward
$beforeMess = substr($ctrl, 0, $messStart);
$afterMess = substr($ctrl, $editImportPos);

// Check if "afterMess" starts correctly - it should start with "function editImport"
// But the original "public function editImport" had "public" before "function"
// so we need to check if there's a "public " right before our afterMess that got orphaned
// Actually, the issue was that the insertion split "public function editImport"
// So afterMess starts with "function editImport" without "public"

// Let's check what's right before afterMess in the original
// The original was: "public function editImport(...)"
// Our cut was at "function editImport" so we lost the "public"

// Actually wait - we cut at "function editImport" position, so afterMess = "function editImport..."
// We need to add "public " back
if (strpos($afterMess, 'function editImport') === 0) {
    $afterMess = '    public ' . $afterMess;
}

$properImportProduct = <<<'METHOD'

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
METHOD;

// Rebuild the file
$fixed = $beforeMess . $properImportProduct . "\n\n" . $afterMess;

if (file_put_contents($ctrlFile, $fixed)) {
    echo '<p style="color:green;font-size:16px;font-weight:bold;">&#10003; Controller fixed successfully!</p>';
} else {
    echo '<p style="color:red;font-size:16px;">&#10007; Failed to write controller</p>';
    die();
}

// Verify
$verify = file_get_contents($ctrlFile);
echo '<h3>Verification:</h3>';
$methods = ['importForm', 'importProduct', 'editImport', 'updateImport'];
foreach ($methods as $m) {
    // Check for "public function methodName" (not double public)
    $pattern = '/public\s+function\s+' . $m . '\s*\(/';
    $clean = preg_match($pattern, $verify);
    $double = preg_match('/public\s+public\s+function/', $verify);
    echo ($clean ? '<span style="color:green">&#10003;</span>' : '<span style="color:red">&#10007;</span>') . ' ' . $m . '()';
    if (!$clean) echo ' <span style="color:red">(format issue)</span>';
    echo '<br>';
}

if (preg_match('/public\s+public\s+function/', $verify)) {
    echo '<span style="color:red;font-weight:bold;">&#10007; DOUBLE PUBLIC FOUND - still broken!</span><br>';
    echo '<pre style="font-size:11px;background:#1e293b;color:#e2e8f0;padding:10px;border-radius:8px;max-height:400px;overflow:auto;">' . htmlspecialchars($verify) . '</pre>';
} else {
    echo '<span style="color:green;font-weight:bold;">&#10003; No double-modifier issues found!</span><br>';
}

echo '</div>';
