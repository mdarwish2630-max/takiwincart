<?php
/*
 * PV_FixImportForm.php
 * Fixes the form action in import-form.blade.php
 * Run: http://localhost/takwincart/PV_FixImportForm.php
 */

$base = __DIR__;
if (!is_dir($base . '/packages/workdo/ProductVault')) {
    $base = dirname(__DIR__);
}

$viewFile = $base . '/packages/workdo/ProductVault/src/Resources/views/dashboard/import-form.blade.php';

echo '<div style="font-family:monospace;font-size:13px;line-height:2;">';
echo '<h2>PV_FixImportForm</h2>';

$v = file_get_contents($viewFile);

// Find all route references
echo '<h3>Current route references in import-form.blade.php:</h3>';
preg_match_all("/route\(['\"]([^'\"]+)['\"]/", $v, $matches);
foreach ($matches[1] as $r) {
    echo '&nbsp;&nbsp;' . $r . '<br>';
}

// Fix: process-import -> import
$fixes = [
    'vault-library.process-import' => 'vault-library.import',
];

$fixed = 0;
foreach ($fixes as $wrong => $correct) {
    if (strpos($v, $wrong) !== false) {
        $v = str_replace($wrong, $correct, $v);
        echo '<br><span style="color:green">&#10003;</span> Changed "' . $wrong . '" → "' . $correct . '"<br>';
        $fixed++;
    } else {
        echo '<br><span style="color:gray">—</span> "' . $wrong . '" not found (already correct)<br>';
    }
}

if ($fixed > 0) {
    if (file_put_contents($viewFile, $v)) {
        echo '<br><span style="color:green;font-weight:bold;">&#10003; File saved!</span><br>';
    } else {
        echo '<br><span style="color:red;">Failed to save file</span>';
    }
} else {
    echo '<br><span style="color:orange;">No fixes needed.</span>';
}

// Also check edit-import.blade.php for wrong routes
$editFile = $base . '/packages/workdo/ProductVault/src/Resources/views/dashboard/edit-import.blade.php';
if (file_exists($editFile)) {
    echo '<h3>Route references in edit-import.blade.php:</h3>';
    $e = file_get_contents($editFile);
    preg_match_all("/route\(['\"]([^'\"]+)['\"]/", $e, $eMatches);
    foreach ($eMatches[1] as $r) {
        echo '&nbsp;&nbsp;' . $r . '<br>';
    }
    
    // Fix any wrong routes in edit-import too
    $editFixes = [
        'vault-library.process-import' => 'vault-library.import',
    ];
    $editFixed = 0;
    foreach ($editFixes as $wrong => $correct) {
        if (strpos($e, $wrong) !== false) {
            $e = str_replace($wrong, $correct, $e);
            $editFixed++;
        }
    }
    if ($editFixed > 0) {
        file_put_contents($editFile, $e);
        echo '<span style="color:green">&#10003;</span> Fixed edit-import.blade.php too<br>';
    }
}

echo '</div>';
