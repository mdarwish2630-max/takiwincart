<?php
/*
 * PV_DiagImport.php - FIXED
 * Auto-detects correct base path
 * Run: http://localhost/takwincart/PV_DiagImport.php
 * OR:   http://localhost/takwincart/public/PV_DiagImport.php
 */

// Auto-detect base path
$possiblePaths = [
    __DIR__,                           // if in public/
    dirname(__DIR__),                  // if in public/ (dirname)
    __DIR__ . '/..',                   // manual parent
    dirname(__DIR__) . '/..',          // double parent
];

$base = null;
foreach ($possiblePaths as $p) {
    $real = realpath($p);
    if ($real && file_exists($real . '/artisan') && file_exists($real . '/packages')) {
        $base = $real;
        break;
    }
}

if (!$base) {
    // Last resort: check if __DIR__ itself has packages
    if (is_dir(__DIR__ . '/packages/workdo/ProductVault')) {
        $base = realpath(__DIR__);
    }
    // Check parent
    if (!$base && is_dir(dirname(__DIR__) . '/packages/workdo/ProductVault')) {
        $base = realpath(dirname(__DIR__));
    }
}

if (!$base) {
    echo '<span style="color:red;font-size:18px;">ERROR: Cannot find project root!</span><br>';
    echo '__DIR__ = ' . __DIR__ . '<br>';
    echo 'dirname(__DIR__) = ' . dirname(__DIR__) . '<br>';
    die('Please check path.');
}

$pkg      = $base . '/packages/workdo/ProductVault';
$viewDir  = $pkg . '/src/Resources/views/dashboard';
$ctrlFile = $pkg . '/src/Http/Controllers/VaultDashboardController.php';
$routeFile = $pkg . '/src/Routes/web.php';

echo '<h2>PV_DiagImport - Import Feature Diagnostic</h2>';
echo '<div style="font-family:monospace;font-size:13px;line-height:2;">';
echo '<b>Detected base:</b> ' . $base . '<br>';
echo '<b>Package:</b> ' . (is_dir($pkg) ? '<span style="color:green">FOUND</span>' : '<span style="color:red">NOT FOUND</span>') . '<br>';
echo '<hr>';

// 1. Check views
echo '<h3>1. Views (dashboard)</h3>';
if (is_dir($viewDir)) {
    $allViews = glob($viewDir . '/*.blade.php');
    echo 'Found ' . count($allViews) . ' view files:<br>';
    foreach ($allViews as $v) {
        $name = basename($v);
        $isImport = (strpos($name, 'import') !== false);
        $color = $isImport ? '#3b82f6' : '#666';
        echo '&nbsp;&nbsp;📄 <span style="color:' . $color . '"><b>' . $name . '</b></span> <span style="color:gray">(' . filesize($v) . ' bytes)</span><br>';
    }
    
    // Specific import views
    echo '<br>';
    $imp = $viewDir . '/import-form.blade.php';
    $edi = $viewDir . '/edit-import.blade.php';
    echo (file_exists($imp) ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>') . ' import-form.blade.php<br>';
    echo (file_exists($edi) ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>') . ' edit-import.blade.php<br>';
} else {
    echo '<span style="color:red">✗</span> View dir not found: ' . $viewDir . '<br>';
}

// 2. Check controller
echo '<h3>2. Controller Methods</h3>';
if (file_exists($ctrlFile)) {
    echo '<span style="color:green">✓</span> Controller found: ' . $ctrlFile . '<br>';
    $ctrl = file_get_contents($ctrlFile);
    $methods = ['importForm', 'importProduct', 'editImport', 'updateImport'];
    foreach ($methods as $m) {
        $found = strpos($ctrl, 'function ' . $m) !== false;
        echo ($found ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>') . ' function ' . $m . '()' . '<br>';
    }
} else {
    echo '<span style="color:red">✗</span> Controller not found: ' . $ctrlFile . '<br>';
    // Try to find it
    $ctrlGlob = glob($pkg . '/src/Http/Controllers/*.php');
    echo '&nbsp;&nbsp;Found controllers: ';
    if ($ctrlGlob) {
        foreach ($ctrlGlob as $c) echo basename($c) . ', ';
    } else {
        echo 'NONE';
    }
    echo '<br>';
}

// 3. Check routes
echo '<h3>3. Routes</h3>';
if (file_exists($routeFile)) {
    echo '<span style="color:green">✓</span> Route file found: ' . $routeFile . '<br>';
    $routes = file_get_contents($routeFile);
    $routeNames = ['import-form', 'import', 'edit-import', 'update-import'];
    foreach ($routeNames as $r) {
        $found = strpos($routes, "'" . $r . "'") !== false || strpos($routes, '"' . $r . '"') !== false;
        echo ($found ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>') . ' route: vault-library.' . $r . '<br>';
    }
    
    echo '<h4>Full web.php:</h4>';
    echo '<pre style="font-size:11px;background:#1e293b;color:#e2e8f0;padding:12px;border-radius:8px;overflow-x:auto;max-height:400px;">' . htmlspecialchars($routes) . '</pre>';
} else {
    echo '<span style="color:red">✗</span> Route file not found: ' . $routeFile . '<br>';
    $routeGlob = glob($pkg . '/src/Routes/*.php');
    echo '&nbsp;&nbsp;Found route files: ';
    if ($routeGlob) {
        foreach ($routeGlob as $r) echo basename($r) . ', ';
    } else {
        echo 'NONE';
    }
    echo '<br>';
}

// 4. Check library.blade.php
echo '<h3>4. library.blade.php Import Buttons</h3>';
$libFile = $viewDir . '/library.blade.php';
if (file_exists($libFile)) {
    $lib = file_get_contents($libFile);
    echo (strpos($lib, 'import-form') !== false ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>') . ' Import button<br>';
    echo (strpos($lib, 'edit-import') !== false ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>') . ' Edit button<br>';
    echo (strpos($lib, '$purchase->imported') !== false ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>') . ' Imported check<br>';
} else {
    echo '<span style="color:red">✗</span> library.blade.php not found<br>';
}

echo '</div>';
