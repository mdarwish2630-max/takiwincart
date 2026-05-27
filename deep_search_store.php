<?php
/**
 * deep_search_store.php
 * Search EVERYWHERE for the store error
 */

$base = 'C:\\xampp\\htdocs\\takwincart';
$count = 0;

function searchFile($path) {
    global $count;
    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    if (!in_array($ext, ['php','js','json','blade.php','vue','ts'])) return;
    
    $c = @file_get_contents($path);
    if (!$c) return;
    
    // Search for various patterns
    $patterns = [
        'select a store',
        'select_store',
        'store first',
        'store.*select',
    ];
    
    foreach ($patterns as $p) {
        if (stripos($c, $p) !== false && stripos($c, 'select') !== false && stripos($c, 'store') !== false) {
            // Skip generic "Select Category" etc.
            if (stripos($c, 'select a store') !== false || stripos($c, 'store first') !== false) {
                $count++;
                echo "FOUND ($count): $path\n";
                $lines = explode("\n", $c);
                foreach ($lines as $i => $line) {
                    if (stripos($line, 'store') !== false && (stripos($line, 'select') !== false || stripos($line, 'first') !== false)) {
                        echo "  L" . ($i+1) . ": " . trim($line) . "\n";
                    }
                }
                echo "\n";
                return;
            }
        }
    }
}

echo "=== Searching entire project for 'select a store' / 'store first' ===\n\n";

// Search key directories
$searchDirs = ['app','config','resources','routes','bootstrap','packages'];
foreach ($searchDirs as $d) {
    $fullDir = $base . '\\' . $d;
    if (!is_dir($fullDir)) continue;
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fullDir));
    foreach ($it as $file) {
        if ($file->isFile()) searchFile($file->getPathname());
    }
}

// Also search vendor for the exact text (might be in a package)
echo "\n=== Searching vendor for exact 'select a store' ===\n\n";
$vendorDir = $base . '\\vendor';
if (is_dir($vendorDir)) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($vendorDir));
    $vCount = 0;
    foreach ($it as $file) {
        if ($vCount > 50) break; // limit
        if (!$file->isFile()) continue;
        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['php','json'])) continue;
        if (strpos($file->getPathname(), 'ProductVault') === false) continue;
        
        $c = @file_get_contents($file->getPathname());
        if ($c && (stripos($c, 'select a store') !== false || stripos($c, 'store first') !== false)) {
            echo "FOUND: " . $file->getPathname() . "\n";
            $vCount++;
        }
    }
    if ($vCount == 0) echo "Not found in vendor.\n";
}

// Check session for flash message
echo "\n=== Current session data ===\n";
echo "(We can't read session from CLI, but check browser DevTools > Application > Session)\n";

// Check the controller constructor
echo "\n=== VaultMarketplaceController first 20 lines ===\n";
$ctrl = $base . '\\packages\\workdo\\ProductVault\\src\\Http\\Controllers\\VaultMarketplaceController.php';
if (file_exists($ctrl)) {
    $lines = explode("\n", file_get_contents($ctrl));
    for ($i = 0; $i < min(20, count($lines)); $i++) {
        echo "  L" . ($i+1) . ": " . $lines[$i] . "\n";
    }
}

// Check if extends a base controller
echo "\n=== Searching for 'extends' in controller ===\n";
$c = file_get_contents($ctrl);
if (preg_match('/class\s+\w+\s+extends\s+(\w+)/', $c, $m)) {
    echo "Extends: " . $m[1] . "\n";
    // Check if parent has __construct with store check
    $parentFile = $base . '\\packages\\workdo\\ProductVault\\src\\Http\\Controllers\\' . $m[1] . '.php';
    if (!file_exists($parentFile)) {
        // Try app namespace
        $parentFile = $base . '\\app\\Http\\Controllers\\' . $m[1] . '.php';
    }
    if (file_exists($parentFile)) {
        echo "Parent file: $parentFile\n";
        $pc = file_get_contents($parentFile);
        if (stripos($pc, '__construct') !== false) {
            echo "Has __construct!\n";
            $pl = explode("\n", $pc);
            $inC = false; $bc = 0;
            foreach ($pl as $i => $line) {
                if (stripos($line, 'function __construct') !== false) { $inC = true; $bc = 0; }
                if ($inC) {
                    echo "  L" . ($i+1) . ": " . $line . "\n";
                    $bc += substr_count($line, '{') - substr_count($line, '}');
                    if ($bc <= 0 && strpos($line, '}') !== false) { $inC = false; }
                }
            }
        }
    } else {
        echo "Parent file not found at expected paths.\n";
    }
}

// Check PlanModuleCheck middleware
echo "\n=== PlanModuleCheck middleware ===\n";
$pmc = $base . '\\app\\Http\\Middleware\\PlanModuleCheck.php';
if (file_exists($pmc)) {
    echo file_get_contents($pmc);
}

// Check the header fix
echo "\n=== Header file - first 15 lines ===\n";
$header = $base . '\\resources\\views\\partision\\header.blade.php';
if (file_exists($header)) {
    $lines = explode("\n", file_get_contents($header));
    for ($i = 0; $i < min(15, count($lines)); $i++) {
        echo "  L" . ($i+1) . ": " . $lines[$i] . "\n";
    }
}

echo "\nDone.\n";
