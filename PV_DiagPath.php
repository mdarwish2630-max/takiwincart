<?php
/*
 * PV_DiagPath.php
 * Finds the correct paths for everything
 * Run: http://localhost/takwincart/PV_DiagPath.php
 */

echo '<h2>Path Diagnostic</h2>';
echo '<div style="font-family:monospace;font-size:13px;line-height:2;">';

echo '<b>__DIR__:</b> ' . __DIR__ . '<br>';
echo '<b>dirname(__DIR__):</b> ' . dirname(__DIR__) . '<br>';

$base = dirname(__DIR__);
echo '<hr>';

// Check if we're in the right place
$files = [
    'artisan'             => $base . '/artisan',
    'public/index.php'    => $base . '/public/index.php',
    'app/Models'          => $base . '/app/Models',
    'packages dir'        => $base . '/packages',
    'ProductVault dir'    => $base . '/packages/workdo/ProductVault',
    'ProductVault src'    => $base . '/packages/workdo/ProductVault/src',
];

echo '<h3>Base path checks</h3>';
foreach ($files as $name => $path) {
    $exists = file_exists($path);
    echo ($exists ? '<span style="color:green">✓</span>' : '<span style="color:red">✗</span>') . ' ' . $name . '<br>';
    echo '&nbsp;&nbsp;&nbsp;&nbsp;<span style="color:gray">' . $path . '</span><br>';
}

// If ProductVault exists, check deeper
$pkg = $base . '/packages/workdo/ProductVault';
if (is_dir($pkg)) {
    echo '<h3>ProductVault structure</h3>';
    function listDir($dir, $prefix = '') {
        $items = @scandir($dir);
        if (!$items) return;
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $full = $dir . '/' . $item;
            $isDir = is_dir($full);
            $icon = $isDir ? '📁' : '📄';
            $size = $isDir ? '' : ' <span style="color:gray">(' . filesize($full) . ' bytes)</span>';
            echo $prefix . $icon . ' <span style="color:' . ($isDir ? '#3b82f6' : '#10b981') . '">' . $item . '</span>' . $size . '<br>';
            if ($isDir && strpos($item, '.') !== 0) {
                // Only go 2 levels deep
                $depth = substr_count($prefix, '&nbsp;');
                if ($depth < 12) {
                    listDir($full, $prefix . '&nbsp;&nbsp;');
                }
            }
        }
    }
    
    listDir($pkg . '/src/Resources/views/dashboard', '&nbsp;&nbsp;');
    
    echo '<h3>Controller check</h3>';
    $ctrlDir = $pkg . '/src/Http/Controllers';
    if (is_dir($ctrlDir)) {
        $ctrlFiles = glob($ctrlDir . '/*.php');
        foreach ($ctrlFiles as $cf) {
            echo '📄 ' . basename($cf) . ' <span style="color:gray">(' . filesize($cf) . ' bytes)</span><br>';
        }
    } else {
        echo '<span style="color:red">✗ Controllers dir not found: ' . $ctrlDir . '</span><br>';
    }
    
    echo '<h3>Routes check</h3>';
    $routeDir = $pkg . '/src/Routes';
    if (is_dir($routeDir)) {
        $routeFiles = glob($routeDir . '/*.php');
        foreach ($routeFiles as $rf) {
            echo '📄 ' . basename($rf) . ' <span style="color:gray">(' . filesize($rf) . ' bytes)</span><br>';
        }
    } else {
        echo '<span style="color:red">✗ Routes dir not found: ' . $routeDir . '</span><br>';
    }
}

echo '</div>';
